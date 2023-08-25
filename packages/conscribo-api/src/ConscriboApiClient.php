<?php

declare(strict_types=1);

namespace Gumbo\ConscriboApi;

use Gumbo\ConscriboApi\Exceptions\ConscriboExceptionFactory;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use LogicException;
use RuntimeException;

class ConscriboApiClient implements Contracts\ConscriboApiClient
{
    public const API_VERSION = '0.20161212';

    protected ConfigRepository $config;

    protected string $apiUrl;

    public function __construct(ConfigRepository $config)
    {
        $this->config = $config;
    }

    public function getSessionId(): string
    {
        throw new LogicException('Not implemented');
    }

    public function getEntityTypes(): array
    {
        throw new LogicException('Not implemented');
    }

    public function users(): RelationQuery
    {
        return $this->resource(Config::get('services.conscribo.entities.user'));
    }

    public function resource(string $resource): RelationQuery
    {
        return new RelationQuery($this, $resource);
    }

    public function groups(): GroupQuery
    {
        return new GroupQuery($this);
    }

    public function makeApiCall(string $method, array $parameters = []): array
    {
        $sessionToken = $this->getSessionId();

        $requestBody = [
            'request' => array_merge($parameters, [
                'command' => $method,
            ]),
        ];

        $result = Http::asJson()
            ->withHeader('X-Conscribo-API-Version', self::API_VERSION)
            ->withHeader('X-Conscribo-Session', $sessionToken)
            ->post($this->getApiEndpointUrl(), $requestBody);

        $body = $result->json('response');
        $success = Arr::get('success', $body);
        if ($success !== 1) {
            throw ConscriboExceptionFactory::buildFromNotification(Arr::get('notifications.notification', $body));
        }

        return $body;
    }

    protected function getApiEndpointUrl(): string
    {
        $account = $this->config->get('services.conscribo.account');
        if (! $account) {
            throw new RuntimeException('No Conscribo account configured');
        }

        return sprintf('https://secure.conscribo.nl/%s/request.json', $account);
    }
}
