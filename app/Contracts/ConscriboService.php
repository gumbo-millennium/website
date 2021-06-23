<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Support\Collection;

interface ConscriboService
{
    /**
     * Creates a new Conscribo service, which will log in to the given account using the provided
     * username and password.
     */
    public function __construct(string $account, string $username, string $password);

    /**
     * Attempts login with the API.
     *
     * @throws ServiceException
     */
    public function authenticate(): void;

    /**
     * Runs the given command on the API.
     *
     * @return null|array
     * @throws HttpExceptionInterface on API failure
     */
    public function runCommand(string $command, array $args): array;

    /**
     * Identical to runCommand, but will raise a `offset` parameter to get all results.
     *
     * @throws HttpExceptionInterface on API failure
     */
    public function runPaginatedCommand(string $command, array $args): array;

    /**
     * Returns types of resources available for this administration.
     *
     * @throws HttpExceptionInterface
     */
    public function getResourceTypes(): array;

    /**
     * Returns fields available for the given resource type.
     *
     * @throws HttpExceptionInterface
     */
    public function getResourceFields(string $resource): array;

    /**
     * Returns the resources of the given type, after applying the
     * filters and optional params.
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
    ): Collection;
}
