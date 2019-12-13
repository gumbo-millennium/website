<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ConscriboContract;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use JsonSchema\Exception\JsonDecodingException;
use LogicException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Communicates with the Conscribo API
 */
final class ConscriboService implements ConscriboContract
{
    private const API_VERSION = '0.20161212';
    private const CACHE_KEY = 'conscribo.api-session-id';

    public const ERR_NO_SESSION = 2000;
    public const ERR_BAD_REQUEST = 2001;
    public const ERR_API_FAILURE = 2010;
    public const ERR_HTTP_FAILURE = 2011;

    /**
     * Messages about expired tokens. They might change in the future
     */
    private const SESSION_EXPIRED_ERRORS = [
        'sessie is verlopen',
    ];

    /**
     * The shared HTTP client
     *
     * @var Client $httpClient
     */
    private $httpClient;

    /**
     * If we're retrying calls
     *
     * @var bool
     */
    private $retry = false;

    /**
     * Base URL
     *
     * @var string
     */
    private $baseUrl;

    /**
     * Creates a new service with an HTTP client
     *
     * @param Client $httpClient
     */
    public function __construct(Client $httpClient)
    {
        // Assign HTTP client
        $this->httpClient = $httpClient;

        // Assign URL
        $this->baseUrl = sprintf(
            'https://secure.conscribo.nl/%s/request.json',
            config('gumbo.conscribo.account-name')
        );
    }

    /**
     * Returns if the API is configured for use.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return !empty(config('gumbo.conscribo.account-name'))
            && !empty(config('gumbo.conscribo.username'))
            && !empty(config('gumbo.conscribo.passphrase'));
    }

    /**
     * Adds headers to the given request
     *
     * @param string $payload
     * @param string|null $sessionId
     * @return Request
     */
    private function buildRequest(string $command, array $payload, string $sessionId = null): Request
    {
        // Build JSON payload
        $payloadJson = json_encode([
            'request' => array_merge(
                ['command' => $command],
                $payload
            )
        ]);

        // Add headers, trimming empty ones
        $headers = array_filter([
            'X-Conscribo-API-Version' => self::API_VERSION,
            'X-Conscribo-SessionId' => $sessionId,
            'Content-Type' => 'application/json; charset=utf-8',
            'Content-Length' => strlen($$payloadJson)
        ]);

        // Build request
        return new Request('POST', $this->baseUrl, $headers, $payload);
    }

    /**
     * Performs the request and returns payload
     *
     * @param Request $request
     * @return array
     * @throws HttpException
     */
    private function sendRequest(Request $request): array
    {
        try {
            // Send request. The Conscribo API returns 200 on errors too, so allow
            // for HTTP errors to cause exceptions
            $response = $this->httpClient->send($request, ['http_errors' => true]);

            // Get body and decode JSON
            $body = $response->getBody();
            $json = json_decode($body, true, 16, JSON_THROW_ON_ERROR);

            // Check the 'success' flag
            if (Arr::get($json, 'success', 0)) {
                return $json;
            }

            // Get error messages
            $errors = Arr::get($json, 'notifications.notification', []);
            $error = Arr::first($errors, null, 'Unknown error');

            // Throw exception
            throw new HttpException(400, $error, null, $response->getHeaders(), self::ERR_API_FAILURE);
        } catch (JsonDecodingException $exception) {
            // Log error
            logger()->warning('Failed to decode JSON from Conscribo API.', [
                'request' => $request,
                'response' => $response,
                'exception' => $exception
            ]);

            // Throw exception
            throw new HttpException(500, 'Failed to parse response from API', $exception, [], self::ERR_HTTP_FAILURE);
        } catch (ConnectException | ClientException $exception) {
            // Log system error
            logger()->warning('HTTP call failed.', [
                'request' => $request,
                'response' => $response,
                'exception' => $exception
            ]);

            // Prep variables
            $errorCode = 504; // default to a gateway timeout
            $errorHeaders = []; // default to empty header set
            if ($exception->hasResponse()) {
                $errorCode = $exception->getResponse()->getStatusCode();
                $errorHeaders = $exception->getResponse()->getHeaders();
            }

            // Throw exception
            throw new HttpException(
                $errorCode,
                "HTTP Request failed: {$exception->getMessage()}",
                $exception,
                $errorHeaders,
                self::ERR_HTTP_FAILURE
            );
        } finally {
            $this->retry = false;
        }

        // This never gets reached.
    }

    /**
     * Get a session ID from the API using username / passphrase combination.
     *
     * @return string|null
     */
    private function getSessionId(): ?string
    {
        try {
            // Prep a login request
            $request = $this->buildRequest('authenticateWithUserAndPass', [
                'userName' => config('gumbo.conscribo.username'),
                'passPhrase' => config('gumbo.conscribo.passphrase'),
            ]);

            // Make the call
            $response = $this->sendRequest($request);

            // Let's hope there's an ID here
            return Arr::get($response, 'sessionId');
        } catch (HttpException $e) {
            // Don't return ID on error.
            return null;
        }
    }

    /**
     * Sends the given command to the Conscribo API.
     *
     * @param string $command
     * @param array $args
     * @return array
     * @throws HttpExceptionInterface on API failure
     */
    public function runCommand(string $command, array $args): array
    {
        // Stop if unavailable
        if (!$this->isAvailable()) {
            throw new LogicException('Service is not configured.', self::ERR_BAD_REQUEST);
        }

        // Only get new token if no token is cached or we're trying with a fresh token
        if (!Cache::has(self::CACHE_KEY) || $this->retry) {
            // Get session id
            $id = $this->getSessionId();

            // We cache for 20 minutes. The official expiration is 30 mins, but lets err on the safe side.
            Cache::put(self::CACHE_KEY, $id, now()->addMinutes(20));
        }

        // Shorthand to not re-request from the cache
        $id = $id ?? Cache::get(self::CACHE_KEY);

        // Throw error if ID is missing
        if (!$id) {
            throw new RuntimeException('Cannot determine client ID', self::ERR_NO_SESSION);
        }

        try {
            // Attempt request
            $request = $this->buildRequest($command, $args, $id);
            return $this->sendRequest($request);
        } catch (HttpException $e) {
            // Retry request if client token has expired
            if (in_array(Str::lower($e->getMessage()), self::SESSION_EXPIRED_ERRORS) && !$this->retry) {
                // Flag for retry and re-send own command
                $this->retry = true;
                return $this->runCommand($command, $args);
            }

            // Otherwise, bubble error
            throw $e;
        } finally {
            // Remove retry flag
            $this->retry = false;
        }
    }
}
