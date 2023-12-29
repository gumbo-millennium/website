<?php

declare(strict_types=1);

namespace App\Services\Conscribo\Contracts;

use App\Services\Conscribo\ResourceQuery;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

interface Client
{
    public function __construct(
        CacheRepository $cache,
        ConfigRepository $config,
    );

    /**
     * Request data from Conscribo, using the given command.
     * @param string $command Name of the command to execute
     * @param array $data Data to submit to Conscribo
     * @return array Result data
     * @throws ConscriboException if an error is returned by Conscribo
     * @throws GuzzleException if the request fails
     * @throws LogicException if the data overrides the command specified in the request
     */
    public function request(string $command, array $data): array;

    /**
     * Request data from Conscribo and map it into a target class.
     *
     * @template T of Fluent|Collection|object
     * @param class-string<T> $target
     * @return object|T
     */
    public function requestInto(string $method, array $data, string $key, string $target): object;

    /**
     * Retuns a query builder to get resources from the Conscribo API.
     */
    public function query(string $resourceName): ResourceQuery;

    /**
     * Returns a query builder to get user resources, using a config-defined resource name.
     */
    public function userQuery(): ResourceQuery;

    /**
     * Returns a query builder to get group resources, using a config-defined resource name.
     */
    public function groupQuery(): ResourceQuery;
}
