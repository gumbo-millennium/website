<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\Activity;
use App\Models\User;
use JsonSchema\Exception\JsonDecodingException;
use Psr\Log\LogLevel;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\RateLimitException;
use Stripe\Exception\UnknownApiErrorException;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Handles API errors
 *
 * @author Roelof Roos <github@roelof.io>
 */
class StripeErrorService
{
    /**
     * These API status codes can be forwarded to the client.
     *
     * This does not include stuff like 403 or 400, since they're a signal to
     * our systems and not to the client.
     */
    private const SAFE_TO_FORWARD_HTTPS = [
        Response::HTTP_NOT_FOUND,
        Response::HTTP_INTERNAL_SERVER_ERROR,
        Response::HTTP_BAD_GATEWAY,
        Response::HTTP_SERVICE_UNAVAILABLE,
        Response::HTTP_GATEWAY_TIMEOUT,
    ];

    /**
     * Log exception with given level
     *
     * @param string $level
     * @param string $message
     * @param ApiErrorException $exception
     * @return void
     */
    private function log(string $level, string $message, ApiErrorException $exception): void
    {
        logger()->log($level, $message, [
            'request' => request(),
            'exception' => $exception,
        ]);
    }

    /**
     * Handle creation exceptions. Does not catch 404 errors
     *
     * @param ApiErrorException $exception
     * @throws HttpException
     * @return void
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    final public function handleCreate(ApiErrorException $exception): void
    {
        $errorHttp = Response::HTTP_INTERNAL_SERVER_ERROR;
        $errorMessage = 'Er is een fout opgetreden bij de communicatie met onze betaalpartner (Stripe).';

        if ($exception instanceof AuthenticationException) {
            // Handle invalid credentials first

            // this is bad. Very bad.
            $this->log(LogLevel::ALERT, 'Stripe systems offline. Credentials seem invalid', $exception);

            // Bubble an HTTP exception
            $errorHttp = Response::HTTP_SERVICE_UNAVAILABLE;
        } elseif ($exception instanceof RateLimitException) {
            // Handle rate limits

            // Report rate limit hit (as warning)
            $this->log('warning', 'Stripe rate limit reached', $exception);

            // Report as temp unavailable
            $errorHttp = Response::HTTP_SERVICE_UNAVAILABLE;
            $errorMessage = 'Onze betaalpartner heeft het momenteel erg druk. Probeer het later opnieuw.';
        } elseif ($exception instanceof UnknownApiErrorException) {
            // Handle HTTP exceptions that might be a 404

            // Report our exception
            $this->log(LogLevel::WARNING, 'Received unknown (potential 404) error from Stripe', $exception);

            // Check if the API reponded with an HTTP code we can safely forward
            $exceptionCode = $exception->getHttpStatus();
            if (in_array($exceptionCode, self::SAFE_TO_FORWARD_HTTPS)) {
                $errorHttp = $exceptionCode;
            }
        } else {
            // Log that we don't really get what it's for.
            $this->log(LogLevel::ERROR, 'Received API error, but don\'t know how to handle it.', $exception);
        }

        // Bubble error
        throw new HttpException($errorHttp, $errorMessage, $exception);
    }

    /**
     * Handles update exceptions, catches 404 errors
     *
     * @param ApiErrorException $exception
     * @throws HttpException
     * @return void
     */
    final public function handleRetrieve(ApiErrorException $exception): void
    {
        try {
            // Forward to create handler
            $this->handleCreate($exception);
        } catch (HttpException $exception) {
            // Ignore if the error is a 404 not found
            if (
                $exception->getStatusCode() === Response::HTTP_NOT_FOUND &&
                $exception->getPrevious() instanceof UnknownApiErrorException
            ) {
                return;
            }

            // Report error
            throw $exception;
        }
    }
}
