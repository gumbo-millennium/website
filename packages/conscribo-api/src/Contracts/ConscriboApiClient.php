<?php

declare(strict_types=1);

namespace Gumbo\ConscriboApi\Contracts;

use Gumbo\ConscriboApi\GroupQuery;
use Gumbo\ConscriboApi\RelationQuery;

interface ConscriboApiClient
{
    /**
     * Retrieves the session ID from the cache or the API.
     *
     * @throws UnauthorizedException
     */
    public function getSessionId(): string;

    /**
     * Returns all available entity types (Relation types) on the API and their corresponding fields.
     * Data will be stored in a cache for 24 hours.
     * The cache key is conscribo.entities.
     *
     * @return array<string,array<string,string>>
     */
    public function getEntityTypes(): array;

    /**
     * Returns a RelationQuery to fetch users, as defined in the services
     * under conscribo.entities.users.
     */
    public function users(): RelationQuery;

    /**
     * Returns a RelationQuery to fetch a given resource.
     */
    public function resource(string $resource): RelationQuery;

    /**
     * Makes an API call and returns the result, unless the result is not succesful.
     */
    public function makeApiCall(string $method, array $parameters = []): array;

    /**
     * List all groups and their relations, or just the given group ID.
     *
     * @param  string  $groupId
     */
    public function groups(): GroupQuery;
}
