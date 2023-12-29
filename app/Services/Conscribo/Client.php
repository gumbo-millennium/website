<?php

declare(strict_types=1);

namespace App\Services\Conscribo;

use App\Services\Conscribo\Contracts\ApiMakeable;
use App\Services\Conscribo\Data\EntityFieldCollection;
use App\Services\Conscribo\Data\EntityGroupCollection;
use App\Services\Conscribo\Data\EntityTypeCollection;
use App\Services\Conscribo\Enums\ConscriboErrorCodes;
use App\Services\Conscribo\Exceptions\ConscriboException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use LogicException;

class Client implements Contracts\Client
{
    public const API_VERSION = '0.20161212';

    public const SESSION_ID_CACHE_KEY = 'conscribo.session_id';

    public const SESSION_ID_CACHE_TTL = 'PT30M';

    // Conscribo API URLs
    private readonly string $baseUrl;

    private readonly string $apiUrl;

    // Session state
    private bool $loggedIn = false;

    private ?string $sessionId = null;

    public function __construct(
        private CacheRepository $cache,
        private ConfigRepository $config,
    ) {
        $this->baseUrl = $config->get('conscribo.base_url');
        $this->apiUrl = "{$config->get('conscribo.account')}/request.json";

        // We can fail in the constructor, Conscribo isn't called from user-level.
        if (empty($config->get('conscribo.account')) || empty($config->get('conscribo.username'))) {
            throw new ConscriboException('Conscribo is not configured.', ConscriboErrorCodes::NotConfigured);
        }

        // Trust the cache to determine if we're logged in (the Conscribo API is stateful)
        if ($cacheKey = $cache->get(self::SESSION_ID_CACHE_KEY)) {
            $this->loggedIn = true;
            $this->sessionId = $cacheKey;
        }
    }

    public function request(string $command, array $data): array
    {
        throw_if(Arr::has($data, 'command', 'requestSequence'), LogicException::class, 'The data overrides system parameters.');

        $commands = [array_merge([
            'requestSequence' => 'user',
            'command' => $command,
        ], $data)];

        // If the user is not logged in, unshift an authentication request
        if (! $this->loggedIn) {
            $this->sessionId = null;

            array_unshift($commands, $this->buildLoginCommand());
        }

        $response = Http::acceptJson()
            ->withHeaders(array_filter([
                'X-Conscribo-Api-Version' => self::API_VERSION,
                'X-Conscribo-SessionId' => $this->sessionId,
            ]))
            ->post(
                "{$this->baseUrl}/{$this->apiUrl}",
                ['requests' => ['request' => $commands]],
            );

        return $this->handleResponse($commands, $response);
    }

    /**
     * Make an API request, and parse the response into a model.
     *
     * @template T of ApiMakeable
     * @param class-string<T> $target
     * @return T
     * @throws ConscriboException if something goes wrong, may include that the data key is missing
     */
    public function requestInto(string $method, array $data, string $key, string $target): ApiMakeable
    {
        if (! is_a($target, ApiMakeable::class, true)) {
            throw new LogicException(sprintf('%s must implement %s', $target, ApiMakeable::class));
        }

        $result = $this->request($method, $data);

        return $target::apiMake(Arr::get($result, $key, []));
    }

    public function query(string $resourceName): ResourceQuery
    {
        // Get the possible entity types
        $entityTypes = $this->cache->remember('conscribo.entity_types', Date::now()->addDay(), function () {
            $entityTypes = $this->getEntityTypes();

            foreach ($entityTypes as $entityTypeName => &$entityType) {
                $entityType['fields'] = $this->getEntityFields($entityTypeName);
            }

            return $entityTypes;
        });

        if (! $resource = $entityTypes->get($resourceName)) {
            throw new LogicException("Invalid resource: {$resourceName}");
        }

        return new ResourceQuery($this, $resource);
    }

    public function userQuery(): ResourceQuery
    {
        return $this->query($this->config->get('conscribo.user_resource'));
    }

    public function listGroups(?int $groupId = null): EntityGroupCollection
    {
        return $this->requestInto('listEntityGroups', array_filter(
            ['groupId' => $groupId],
        ), 'entityGroups', EntityGroupCollection::class);
    }

    protected function getEntityTypes(): EntityTypeCollection
    {
        return $this->requestInto('listEntityTypes', [], 'entityTypes', EntityTypeCollection::class);
    }

    protected function getEntityFields(string $entityType): EntityFieldCollection
    {
        return $this->requestInto('listFieldDefinitions', ['entityType' => $entityType], 'fields', EntityFieldCollection::class);
    }

    private function buildLoginCommand(): array
    {
        return [
            'command' => 'authenticateWithUserAndPass',
            'requestSequence' => 'auth',
            'userName' => $this->config->get('conscribo.username'),
            'passPhrase' => $this->config->get('conscribo.password'),
        ];
    }

    private function handleResponse(array $commands, Response $response): array
    {
        $commandDebugName = implode(' + ', array_column($commands, 'command'));

        if (! $response->successful()) {
            throw ConscriboException::fromHttpResponse($response, $commandDebugName);
        }

        $results = Collection::make($response->json('results.result'))->keyBy('requestSequence');

        // Handle login first, since it can fall through to the user request
        if (! $this->loggedIn) {
            $authResponse = $results->get('auth');
            if (! $authResponse['success']) {
                throw ConscriboException::fromHttpResponse($response, $commandDebugName);
            }

            $this->loggedIn = true;
            $this->sessionId = $authResponse['sessionId'];

            $this->cache->put(self::SESSION_ID_CACHE_KEY, $this->sessionId, Date::now()->add(self::SESSION_ID_CACHE_TTL));
        }

        $userResponse = $results->get('user');
        if ($userResponse['success']) {
            return $userResponse;
        }

        $exception = ConscriboException::fromHttpResponse($response, $commandDebugName);

        // Retry the request if the session is invalid
        if ($exception->getConscriboCode() === ConscriboErrorCodes::AuthExpired) {
            $this->loggedIn = false;
            $this->sessionId = null;

            $commandToRun = Arr::last($commands);

            return $this->request(
                $commandToRun['command'],
                Arr::except($commandToRun, ['command', 'requestSequence']),
            );
        }

        throw $exception;
    }
}
