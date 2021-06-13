<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ConscriboService as ConscriboServiceContract;
use DateTimeInterface;
use GuzzleHttp\Client as HttpClient;
use function GuzzleHttp\Psr7\stream_for;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use InvalidArgumentException;
use JsonException;
use RuntimeException;

/**
 * Communicates with the Conscribo API.
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

    private const TTL_VALID = 60 * 15; // Cache session ID for 15 minutes

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

    /**
     * Base URL, or null if no account is known.
     */
    private ?string $endpoint;

    private bool $retry = false;

    /**
     * Session ID obtained from the API or cache.
     */
    private ?string $sessionId = null;

    /**
     * Login username.
     */
    private string $username;

    /**
     * Login password.
     */
    private string $password;

    /**
     * Guzzle client.
     */
    private HttpClient $http;

    /**
     * Returns a service from data in the config. Throws a fit
     * if the config is missing.
     *
     * @return ConscriboService
     * @throws RuntimeException
     */
    public static function fromConfig(): self
    {
        $account = Config::get('services.conscribo.account');
        $username = Config::get('services.conscribo.username');
        $password = Config::get('services.conscribo.password');

        if (empty($account) || empty($username) || empty($password)) {
            throw new RuntimeException('Conscribo not configured');
        }

        return new self($account, $username, $password);
    }

    /**
     * Creates service.
     */
    public function __construct(
        ?string $account = null,
        ?string $username = null,
        ?string $password = null,
        ?HttpClient $http = null
    ) {
        // Get data from config
        $account ??= Config::get('services.conscribo.account');
        $username ??= Config::get('services.conscribo.username');
        $password ??= Config::get('services.conscribo.password');
        // Check
        if (! $account || empty($username) || empty($password)) {
            throw new InvalidArgumentException('Expected a username, password and account.');
        }

        // Assign credentials
        $this->username = $username;
        $this->password = $password;

        // Assign URL
        $this->endpoint = $account ? "https://secure.conscribo.nl/{$account}/request.json" : null;

        // Load key from cache
        $this->sessionId = Cache::get(self::CACHE_KEY);

        // Assign client
        $this->http = $http ?? new HttpClient([
            RequestOptions::HTTP_ERRORS => false,
        ]);
    }

    /**
     * Attempts login with the API.
     *
     * @throws ServiceException
     */
    public function authenticate(): void
    {
        // Prep body
        $body = $this->buildBody('authenticateWithUserAndPass', [
            'userName' => $this->username,
            'passPhrase' => $this->password,
        ]);

        // Get headers
        $headers = $this->buildHeaders(null);

        // Send request
        $psrResponse = $this->http->post($this->endpoint, [
            'body' => stream_for($body),
            'headers' => $headers,
        ]);

        // Decode body
        try {
            $response = json_decode($psrResponse->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $response = [];
        }

        // Throw a fit
        if ($psrResponse->getStatusCode() !== 200 || ! Arr::has($response, 'result.sessionId')) {
            throw new RuntimeException('Failed to authenticate against Conscribo API');
        }

        // Get session ID
        $sessionId = Arr::get($response, 'result.sessionId');

        // Cache new ID
        Cache::put(self::CACHE_KEY, $sessionId, now()->addSeconds(self::TTL_VALID));

        // Assign and store for 25 minutes
        $this->sessionId = $sessionId;
    }

    /**
     * Sends the given command to the Conscribo API.
     *
     * @throws HttpExceptionInterface on API failure
     */
    public function runCommand(string $command, array $args): array
    {
        // Make first session ID request
        if (! $this->sessionId || $this->retry) {
            $this->authenticate();
            $this->retry = false;
        }

        // Prep fields
        $body = $this->buildBody($command, $args);
        $headers = $this->buildHeaders($this->sessionId);

        // Send request
        $psrResponse = $this->http->post($this->endpoint, [
            'body' => stream_for($body),
            'headers' => $headers,
        ]);

        // Decode body
        try {
            $response = json_decode($psrResponse->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $response = [];
        }

        // Skip server failures
        if ($psrResponse->getStatusCode() >= 500) {
            throw new RuntimeException('Service seems unavailable');
        }

        // Get Ok
        $result = Arr::get($response, 'result', []);
        $ok = Arr::get($result, 'success');
        if ($ok) {
            return $result;
        }

        // Get error
        $error = Str::lower(Arr::get($result, 'notifications.notification.0', 'unknown error'));
        if (Str::contains($error, ['not authenticated', 'session is verlopen']) && ! $this->retry) {
            $this->retry = true;

            return $this->runCommand($command, $args);
        }

        // Throw an exception
        throw new RuntimeException("Command failed: {$error}");
    }

    /**
     * Identical to runCommand, but will raise a `offset` parameter to get all results.
     */
    public function runPaginatedCommand(string $command, array $args): array
    {
        // Output
        $results = [];

        // Pagination
        $limit = 25;
        $offset = 0;

        // Smart discovery
        $totalResults = null;
        $resultKey = null;
        do {
            $response = $this->runCommand($command, array_merge($args, [
                'limit' => $limit,
                'offset' => $offset,
            ]));

            // Skip if failed
            if (! $response) {
                break;
            }

            // Determine total
            if ($totalResults === null) {
                $totalResults = Arr::get($response, 'resultCount') ?? $limit * 2;
            }

            // Find key if no key yet
            if ($resultKey === null) {
                foreach ($response as $row => $value) {
                    if (is_array($value)) {
                        $resultKey = $row;

                        break;
                    }
                }

                // Fail if no key was found
                if (! $resultKey) {
                    break;
                }
            }

            // Get result in key
            $results[] = Arr::get($response, $resultKey);

            // Raise offset
            $offset += $limit;
        } while ($offset < $totalResults);

        // Collapse and return
        return Arr::collapse($results);
    }

    /**
     * Returns types available.
     *
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
     * Returns fields for the given type.
     *
     * @throws HttpExceptionInterface
     */
    public function getResourceFields(string $type): array
    {
        // Check config for an override
        $configType = config("services.conscribo.resources.{$type}");
        if (! empty($configType)) {
            $type = $configType;
        }

        $types = $this->getResourceTypes();
        if (! in_array($type, $types, true)) {
            throw new InvalidArgumentException("Type {$type} is not a valid resource type");
        }

        $cacheKey = sprintf(self::CACHE_TYPE_FIELDS, $type);
        $cachedTypes = Cache::get($cacheKey);
        if ($cachedTypes) {
            return $cachedTypes;
        }

        // Run command
        $response = $this->runCommand('ListFieldDefinitions', [
            'entityType' => $type,
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
     * Returns a list of resources of the specified type.
     *
     * @param array<array> $filters
     * @param array<string> $fields
     * @param array<scalar> $options
     * @throws HttpExceptionInterface
     * @throws InvalidArgumentException
     */
    public function getResource(
        string $type,
        array $filters = [],
        array $fields = [],
        array $options = []
    ): Collection {
        // Check config for an override
        $configType = config("services.conscribo.resources.{$type}");
        if (! empty($configType)) {
            $type = $configType;
        }

        // Check if type exists
        $types = $this->getResourceTypes();
        if (! in_array($type, $types, true)) {
            throw new InvalidArgumentException("Type {$type} is not a valid resource type");
        }

        // Get fields for type
        $resourceFields = $this->getResourceFields($type);

        foreach ($fields as $fieldName) {
            if (! \array_key_exists($fieldName, $resourceFields)) {
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
                'fieldName' => Arr::pluck($requestedFields, 'fieldName'),
            ],
            'limit' => 100,
        ];

        // Merge options
        $arguments = array_merge($arguments, Arr::only($options, ['codes', 'limit', 'offset']));

        // Prep filters
        $safeFilters = $this->buildFilters($resourceFields, $filters);

        // Add filters to request
        if (! empty($safeFilters)) {
            $arguments['filters'] = [
                'filter' => $safeFilters,
            ];
        }

        // Run request
        $result = $this->runPaginatedCommand('listRelations', $arguments);

        // Map to model
        $out = $this->buildModels($resourceFields, $result);

        // Map members if a 'leden' item is present
        foreach ($out as $key => $row) {
            if (! Arr::has($row, 'leden')) {
                continue;
            }

            // Map members to IDs
            $row['members'] = collect(explode(',', $row['leden'] ?? ''))
                ->map('trim')
                ->map(static fn ($val) => explode(':', $val, 2)[0])
                ->map(static fn ($val) => filter_var($val, \FILTER_VALIDATE_INT))
                ->values()
                ->all();

            // Re-assign value
            $out->put($key, $row);
        }

        // Done for real
        return $out;
    }

    /**
     * Returns an array of groups with.
     */
    public function getResourceGroups(string $resourceType): array
    {
        // Run command
        $response = $this->runPaginatedCommand('ListEntityGroups', []);

        // Check all groups
        if (empty($response)) {
            return [];
        }

        $groups = [];
        foreach ($response as $group) {
            // Get props
            $id = Arr::get($group, 'id');
            $type = Arr::get($group, 'type');
            $name = Arr::get($group, 'name');
            $slug = Str::slug($name);

            // Skip non-processable
            if (empty($slug) || $type === 'archive') {
                continue;
            }

            // Map members
            $members = [];
            foreach (Arr::get($group, 'members', []) as $member) {
                if ($member['entityType'] !== $resourceType) {
                    continue;
                }

                $members[] = filter_var($member['entityId'], \FILTER_VALIDATE_INT);
            }

            // Map to array
            $groups[$slug] = compact('id', 'name', 'members');
        }

        // Return groups
        return $groups;
    }

    /**
     * Validates filters and converts them to something the API can handle with.
     * Allowed formats:
     * ['field' => 'value'], [0 => ['field', 'value']], [0 => ['field', 'operator', 'value']].
     *
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
            if (! \array_key_exists($fieldName, $fields)) {
                throw new InvalidArgumentException(
                    "Filter at index [{$key}] tried to filter field [{$fieldName}]. which does not exist"
                );
            }

            // Ensure operator exists
            if (! array_key_exists($operator, self::FILTER_OPERATOR_MAP)) {
                throw new InvalidArgumentException(
                    "Filter at index [{$key}] has operator [{$operator}]. which is invalid."
                );
            }

            // Ensure operator is usable on field
            $fieldType = $fields[$fieldName]['type'];
            if (! in_array($fieldType, self::FILTER_OPERATOR_MAP[$operator], true)) {
                throw new InvalidArgumentException(
                    "Filter at index [{$key}] has operator [{$operator}]. which is invalid for data type [${fieldType}]."
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
     * easy to use in PHP (dates as \DateTimeInterface, and such). Complexity: O(n²).
     *
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
                if (! isset($fields[$field])) {
                    throw new RuntimeException("Recieved unknown field [{$field}] from API");
                }

                // Mutate data according to format
                $fieldType = $fields[$field]['type'] ?? 'string';

                // Treat dates as immutable
                if ($fieldType === 'date' && $value !== '0000-00-00' && ! empty($value)) {
                    $newRow[$field] = Date::createFromFormat('Y-m-d', $value)->setTime(0, 0)->toImmutable();

                    continue;
                }

                // Date '0000-00-00' is just null
                if ($fieldType === 'date') {
                    $newRow[$field] = null;

                    continue;
                }

                // Checkboxes are stored as 0 or 1
                if ($fieldType === 'checkbox') {
                    $newRow[$field] = "{$value}" === '1';

                    continue;
                }

                // Just parse the numbers
                if ($fieldType === 'number' && strlen($fieldType) < 7) {
                    $isFloat = \strpos($value, '.');
                    $newRow[$field] = \filter_var($value, $isFloat ? \FILTER_VALIDATE_FLOAT : \FILTER_VALIDATE_INT);

                    continue;
                }

                // Something else, just write it down
                $newRow[$field] = $value;
            }

            // Insert row
            $newData->put($index, $newRow);
        }

        // Clean, formatted data
        return $newData;
    }

    /**
     * Converts params to a JSON body.
     *
     * @param string $command command to run
     * @param array $params params for the command
     */
    private function buildBody(string $command, array $params): string
    {
        return \json_encode([
            'request' => array_merge(['command' => $command], $params),
        ]);
    }

    /**
     * Returns headers.
     *
     * @return array<string>
     */
    private function buildHeaders(?string $sessionId): array
    {
        $headers = [
            'X-Conscribo-API-Version' => self::API_VERSION,
            'Content-Type' => 'application/json; charset=utf-8',
        ];
        if ($sessionId) {
            $headers['X-Conscribo-SessionId'] = $sessionId;
        }

        return $headers;
    }
}
