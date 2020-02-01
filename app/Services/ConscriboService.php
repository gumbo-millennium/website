<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ConscriboServiceContract;
use App\Exceptions\ServiceException;
use App\Helpers\Arr;
use App\Helpers\Str;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Cache;
use JsonSchema\Exception\JsonDecodingException;
use LogicException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Communicates with the Conscribo API
 */
final class ConscriboService implements ConscriboServiceContract
{
    private const API_VERSION = '0.20161212';
    private const CACHE_KEY = 'conscribo.api-session-id';

    private const TTL_VALID = 60 * 25; // Cache session ID for 25 minutes
    private const TTL_INVALID = 60 * 60 * 6; // Cache flag of invalid creds for 6 hours

    public const ERR_NO_SESSION = 2000;
    public const ERR_BAD_REQUEST = 2001;
    public const ERR_API_FAILURE = 2010;
    public const ERR_HTTP_FAILURE = 2011;

    // Session IDs to tell software somethings wrong
    private const SESSION_ID_INVALID = "\0invalid";

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
     * Flags if we've ever burned the session ID, which can only
     * happen once.
     *
     * @var bool
     */
    private bool $burned = false;

    /**
     * Base URL
     *
     * @var string
     */
    private $baseUrl;

    private ?string $sessionId = null;

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
            'Content-Length' => strlen($payloadJson)
        ]);

        // Build request
        return new Request('POST', $this->baseUrl, $headers, $payloadJson);
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
            dd($request, $request->getBody()->getContents());
            // Send request. The Conscribo API returns 200 on errors too, so allow
            // for HTTP errors to cause exceptions
            $response = $this->httpClient->send($request, ['http_errors' => true]);

            // Get body and decode JSON
            $body = $response->getBody();
            $json = json_decode($body->getContents(), true, 16, JSON_THROW_ON_ERROR);

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
     * Re-authorize
     * @return void
     * @throws ServiceException
     */
    protected function forceReauth()
    {
        // Only allow burning once.-m-24
        if ($this->burned) {
            throw new ServiceException(ServiceException::SERVICE_CONSCRIBO, 'Trying to burn already burnt session', 403);
        }

        // Trash ID
        $this->sessionId = null;
        $this->burned = true;
        Cache::forget(self::CACHE_KEY);

        // Re-auth
        return $this->authorise();
    }

    /**
     * Attempts login with the API
     * @return void
     * @throws ServiceException
     */
    public function authorise(): void
    {
        // Check cache key
        $cachedKey = Cache::get(self::CACHE_KEY);

        // Throw exception on cache item
        if ($cachedKey === self::SESSION_ID_INVALID) {
            throw new ServiceException(ServiceException::SERVICE_CONSCRIBO, 'Faield to obtain session');
        }

        // Assign from cache
        if ($cachedKey) {
            $this->sessionId = $cachedKey;
            return;
        }

        // Get session id
        try {
            // Prep a login request
            $request = $this->buildRequest('authenticateWithUserAndPass', [
                'userName' => config('gumbo.conscribo.username'),
                'passPhrase' => config('gumbo.conscribo.passphrase'),
            ]);

            // Make the call
            $response = $this->sendRequest($request);

            // Get session ID
            $sessionId = Arr::get($response, 'sessionId');

            // Get response
            logger()->debug('Recieved session ID form request: {session-id}', [
                'response' => $response,
                'session-id' => $sessionId
            ]);

            // Assign and store for 25 minutes
            $this->sessionId = $sessionId;
            Cache::put(self::CACHE_KEY, $sessionId, now()->addSeconds(self::TTL_VALID));
        } catch (HttpException $exception) {
            // Cache invalid credentials
            $sessionId = self::SESSION_ID_INVALID;
            Cache::put(self::CACHE_KEY, $sessionId, now()->addSeconds(self::TTL_INVALID));

            // Build service exception
            $exception = new ServiceException(
                ServiceException::SERVICE_CONSCRIBO,
                'Failed to get session',
                $exception->getStatusCode(),
                $exception
            );

            // Report new exception
            \report($exception);

            // Throw it too,
            throw $exception;
        }
    }

    /**
     * Get a session ID from the API using username / passphrase combination.
     *
     * @return string|null
     */
    private function getSessionId(): ?string
    {
        // Log message
        logger()->debug('Retrieving session ID');

        try {
            // Prep a login request
            $request = $this->buildRequest('authenticateWithUserAndPass', [
                'userName' => config('gumbo.conscribo.username'),
                'passPhrase' => config('gumbo.conscribo.passphrase'),
            ]);

            // Make the call
            $response = $this->sendRequest($request);

            // Get session ID
            $sessionId = Arr::get($response, 'sessionId');

            dd($sessionId, $response);

            // Get response
            logger()->debug('Recieved session ID form request: {session-id}', [
                'response' => $response,
                'session-id' => $sessionId
            ]);

            // Let's hope there's an ID here
            return $sessionId;
        } catch (HttpException $exception) {
            // Report error
            \report($exception);

            dd($exception);

            // Log error
            logger()->debug('Failed to get ID from request: {exception}', compact('exception'));

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
            $sessionId = $this->getSessionId();

            // We cache for 20 minutes. The official expiration is 30 mins, but lets err on the safe side.
            Cache::put(self::CACHE_KEY, $sessionId, now()->addMinutes(20));
        }

        // Shorthand to not re-request from the cache
        $sessionId = $sessionId ?? Cache::get(self::CACHE_KEY);

        // Throw error if ID is missing
        if (!$sessionId) {
            throw new RuntimeException('Cannot determine client ID', self::ERR_NO_SESSION);
        }

        try {
            // Attempt request
            $request = $this->buildRequest($command, $args, $sessionId);
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
