<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ConscriboServiceContract;
use App\Exceptions\ServiceException;
use App\Helpers\Arr;
use App\Helpers\Str;
use DateTimeInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use JsonSchema\Exception\JsonDecodingException;
use LogicException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Communicates with the Conscribo API
 */
final class ConscriboService implements ConscriboServiceContract
{
    public const ERR_NO_SESSION = 2000;
    public const ERR_BAD_REQUEST = 2001;
    public const ERR_API_FAILURE = 2010;
    public const ERR_HTTP_FAILURE = 2011;

    private const API_VERSION = '0.20161212';
    private const CACHE_KEY = 'conscribo.api-session-id';
    private const CACHE_TYPES = 'conscribo.api.types';
    private const CACHE_TYPE_FIELDS = 'conscribo.api.fields.%s';

    private const TTL_VALID = 60 * 25; // Cache session ID for 25 minutes
    private const TTL_INVALID = 60 * 60 * 6;// Cache flag of invalid creds for 6 hours

    private const FILTER_OPERATOR_MAP = [
        '=' => ['text', 'textarea', 'mailadres', 'number', 'checkbox', 'multicheckbox', 'enum'],
        '~' => ['text', 'textarea', 'mailadres'],
        '!~' => ['text', 'textarea', 'mailadres'],
        '|~' => ['text', 'textarea', 'mailadres'],
        '+' => ['text', 'textarea', 'mailadres'],
        '-' => ['text', 'textarea', 'mailadres'],
        '><' => ['date'],
        '>=' => ['date'],
        '<=' => ['date'],
        'in' => ['multicheckbox'],
        'all' => ['multicheckbox'],
        '<>' => ['multicheckbox'],
    ];

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
     * @var Client $httpClient
     */
    private Client $httpClient;

    /**
     * Flags if we've ever burned the session ID, which can only
     * happen once.
     * @var bool
     */
    private bool $burned = false;

    /**
     * Base URL, or null if no account is known
     */
    private ?string $baseUrl;

    /**
     * Session ID obtained from the API or cache
     */
    private ?string $sessionId = null;

    /**
     * Login username
     * @var string
     */
    private string $username;

    /**
     * Login password
     * @var string
     */
    private string $password;

    /**
     * Creates service
     * @param string $account
     * @param string $username
     * @param string $password
     * @param null|Client $httpClient
     */
    public function __construct(string $account, string $username, string $password, ?Client $httpClient = null)
    {
        // Assign credentials
        $this->username = $username;
        $this->password = $password;

        // Assign HTTP client
        $this->httpClient = $httpClient;

        // Assign URL
        $this->baseUrl = $account ? "https://secure.conscribo.nl/{$account}/request.json" : null;
    }

    /**
     * Returns if the API is configured for use.
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->baseUrl && !empty($this->username) && !empty($this->password);
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
            throw new ServiceException(ServiceException::SERVICE_CONSCRIBO, 'Failed to obtain session');
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
                'userName' => $this->username,
                'passPhrase' => $this->password,
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
     * Sends the given command to the Conscribo API.
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

        // Throw error if ID is missing
        if (!$this->sessionId) {
            throw new RuntimeException('Cannot determine client ID', self::ERR_NO_SESSION);
        }

        try {
            // Attempt request
            $request = $this->buildRequest($command, $args, $this->sessionId);
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

    /**
     * Returns types available
     * @return array
     * @throws HttpExceptionInterface
     */
    public function getResourceTypes(): array
    {
        $cachedTypes = Cache::get(self::CACHE_TYPES);
        if ($cachedTypes) {
            return $cachedTypes;
        }

        // Run command
        $response = $this->runCommand('ListEntityTypes', []);

        // Get only the type names
        $types = Arr::pluck(Arr::get($response, 'entityTypes'), 'typeName');

        // Cache for a day
        Cache::put(self::CACHE_TYPES, $types, now()->addDay());

        // Return types
        return $types;
    }

    /**
     * Returns fields for the given type
     * @param string $type
     * @return array
     * @throws HttpExceptionInterface
     */
    public function getResourceFields(string $type): array
    {
        // Check config for an override
        $configType = config("services.conscribo.resources.{$type}");
        if (!empty($configType)) {
            $type = $configType;
        }

        $types = $this->getResourceTypes();
        if (!in_array($type, $types)) {
            throw new \InvalidArgumentException("Type {$type} is not a valid resource type");
        }

        $cacheKey = sprintf(self::CACHE_TYPE_FIELDS, $type);
        $cachedTypes = Cache::get($cacheKey);
        if ($cachedTypes) {
            return $cachedTypes;
        }

        // Run command
        $response = $this->runCommand('ListFieldDefinitions', [
            'entityType' => $type
        ]);

        // Key the stuff by field name
        $fields = collect(Arr::get($response, 'fields'))
            ->keyBy('fieldName')
            ->toArray();

        // Cache for a day
        Cache::put($cacheKey, $fields, now()->addDay());

        // Return types
        return $fields;
    }

    /**
     * Returns a list of resources of the specified type
     * @param string $type
     * @param array<array> $filters
     * @param array<string> $fields
     * @param array<scalar> $options
     * @return Collection
     * @throws HttpExceptionInterface
     * @throws InvalidArgumentException
     */
    public function getResource(string $type, array $filters = [], array $fields = [], array $options = []): Collection
    {
        // Check config for an override
        $configType = config("services.conscribo.resources.{$type}");
        if (!empty($configType)) {
            $type = $configType;
        }

        // Check if type exists
        $types = $this->getResourceTypes();
        if (!in_array($type, $types)) {
            throw new \InvalidArgumentException("Type {$type} is not a valid resource type");
        }

        // Get fields for type
        $resourceFields = $this->getResourceFields($type);

        foreach ($fields as $fieldName) {
            if (!\array_key_exists($fieldName, $resourceFields)) {
                throw new RuntimeException("Field [{$fieldName}] does not exist, so cannot be requested.");
            }
        }

        // Get requested fields
        $requestedFields = Arr::only($resourceFields, $fields ?: \array_keys($resourceFields));
        sort($requestedFields);

        // Build basic arguments
        $arguments = [
            'entityType' => $type,
            'requestedFields' => [
                'fieldName' => Arr::pluck($requestedFields, 'fieldName')
            ],
            'limit' => 100
        ];

        // Merge options
        $arguments = array_merge($arguments, Arr::only($options, ['codes', 'limit', 'offset']));

        // Prep filters
        $safeFilters = $this->buildFilters($resourceFields, $filters);

        // Add filters to request
        if (!empty($safeFilters)) {
            $arguments['filters'] = [
                'filter' => $safeFilters
            ];
        }

        // Run request
        $response = $this->runCommand('listRelations', $arguments);
        $result = Arr::get($response, 'relations', []);

        return $this->buildModels($resourceFields, $result);
    }

    /**
     * Validates filters and converts them to something the API can handle with.
     * Allowed formats:
     * ['field' => 'value'], [0 => ['field', 'value']], [0 => ['field', 'operator', 'value']]
     * @param array $fields
     * @param array $filters
     * @return array
     * @throws InvalidArgumentException
     */
    protected function buildFilters(array $fields, array $filters): array
    {
        // Return clean filters
        $cleanFilters = [];

        // Map filters to safe filters
        foreach ($filters as $key => $filter) {
            // Normalise filter
            if (is_scalar($filter)) {
                $filter = [$key, '=', $filter];
            } elseif (count($filter) === 2) {
                $filter = [$filter[0], '=', $filter[1]];
            } elseif (count($filter) !== 3) {
                throw new InvalidArgumentException(
                    "Filter at index {$key} is not a valid filter!"
                );
            }

            // Ensure field exists
            [$fieldName, $operator, $value] = $filter;
            if (!\array_key_exists($fieldName, $fields)) {
                throw new InvalidArgumentException(
                    "Filter at index [{$key}] tried to filter field [{$fieldName}]. which does not exist"
                );
            }

            // Ensure operator exists
            if (!array_key_exists($operator, self::FILTER_OPERATOR_MAP)) {
                throw new InvalidArgumentException(
                    "Filter at index [{$key}] has operator [{$operator}]. which is invalid."
                );
            }

            // Ensure operator is usable on field
            $fieldType = $fields[$fieldName]['type'];
            if (!in_array($fieldType, self::FILTER_OPERATOR_MAP[$operator])) {
                throw new InvalidArgumentException(
                    "Filter at index [{$key}] has operator [{$operator}]. which is invalid for data type [$fieldType]."
                );
            }

            // Format timestamps
            if ($value instanceof DateTimeInterface) {
                $value = $value->format('Y-m-d');
            }

            // Add item
            $cleanFilters[] = compact('fieldName', 'operator', 'value');
        }

        // Done :)
        return $cleanFilters;
    }

    /**
     * Convert a collection of raw API data to a collection of data formatted to be
     * easy to use in PHP (dates as \DateTimeInterface, and such). Complexity: O(n²)
     * @param array<array> $fields
     * @param array<array> $data
     * @return Collection<array>
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    protected function buildModels(array $fields, array $data): Collection
    {
        // Prepare valid data
        $newData = collect();

        foreach ($data as $index => $row) {
            // Prepare a new set
            $newRow = [];

            // Iterate each field
            foreach ($row as $field => $value) {
                if (!isset($fields[$field])) {
                    throw new RuntimeException("Recieved unknown field [{$field}] from API");
                }

                // Mutate data according to format
                $fieldType = $fields[$field]['type'] ?? 'string';
                if ($fieldType === 'date' && $value !== '0000-00-00' && !empty($value)) {
                    $value = Carbon::createFromFormat('Y-m-d', $value)->setTime(0, 0)->toImmutable();
                } elseif ($fieldType === 'date') {
                    $value = null;
                } elseif ($fieldType === 'checkbox') {
                    $value = "{$value}" === '1';
                } elseif ($fieldType  === 'number') {
                    $value = \floatval($value);
                }

                // Assign new row
                $newRow[$field] = $value;
            }

            // Insert row
            $newData->put($index, $newRow);
        }

        // Clean, formatted data
        return $newData;
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
            throw new ServiceException(
                ServiceException::SERVICE_CONSCRIBO,
                'Cannot renew session token',
                409
            );
        }

        // Trash ID
        $this->sessionId = null;
        $this->burned = true;
        Cache::forget(self::CACHE_KEY);

        // Re-auth
        return $this->authorise();
    }

    /**
     * Performs the request and returns payload
     * @param Request $request
     * @return array
     * @throws HttpException
     */
    protected function sendRequest(Request $request): array
    {
        try {
            // Send request. The Conscribo API returns 200 on errors too, so allow
            // for HTTP errors to cause exceptions
            $response = $this->httpClient->send($request, ['http_errors' => true]);

            // Get body and decode JSON
            $body = $response->getBody()->getContents();
            $json = json_decode($body, true, 16, JSON_THROW_ON_ERROR);
            $json = $json['result'] ?? $json;

            // Check the 'success' flag
            if (Arr::get($json, 'success', 0)) {
                return $json;
            }

            // Get error messages
            $errors = Arr::get($json, 'notifications.notification', []);
            $error = Arr::first($errors, null, 'Unknown error');

            // Re-auth if it's an auth error
            if ($error === 'Not authenticated') {
                echo "Re-autorising.\n\n";

                // Retry auth, can only be called once
                $this->forceReauth();

                // Rewind body
                $request->getBody()->rewind();

                // Re-send request
                return $this->sendRequest($request);
            }

            // Debug
            $message = (new MessageFormatter(MessageFormatter::DEBUG))->format($request, $response);
            print("\n\n$message\n\n");

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
        } catch (ConnectException | RequestExceptionInterface $exception) {
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
        Log::error('Unreachable code has been reached!', ['service' => $this]);
    }

    /**
     * Adds headers to the given request
     * @param string $payload
     * @param string|null $sessionId
     * @return Request
     */
    private function buildRequest(string $command, array $payload, ?string $sessionId = null): Request
    {
        // Build request
        $requestData = array_merge(
            ['command' => $command],
            $payload
        );

        // Build response
        $payloadJson = json_encode(['request' => $requestData], \JSON_PRETTY_PRINT);

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
}
