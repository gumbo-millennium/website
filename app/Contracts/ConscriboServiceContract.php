<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Support\Collection;

interface ConscriboServiceContract
{
    /**
     * Creates a new Conscribo service, which will log in to the given account using the provided
     * username and password.
     * @param string $account
     * @param string $username
     * @param string $password
     */
    public function __construct(string $account, string $username, string $password);

    /**
     * Returns if the API is configured for use.
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * Attempts login with the API
     * @return void
     * @throws ServiceException
     */
    public function authorise(): void;

    /**
     * Runs the given command on the API
     * @param string $command
     * @param array $args
     * @return array|null
     * @throws HttpExceptionInterface on API failure
     */
    public function runCommand(string $command, array $args): array;

    /**
     * Returns types of resources available for this administration
     * @return array
     * @throws HttpExceptionInterface
     */
    public function getResourceTypes(): array;

    /**
     * Returns fields available for the given resource type
     * @return array
     * @throws HttpExceptionInterface
     */
    public function getResourceFields(string $resource): array;

    /**
     * Returns the resources of the given type, after applying the
     * filters and optional params
     * @param string $type
     * @param array<array> $filters
     * @param array<string> $fields
     * @param array<scalar> $options
     * @return Collection
     * @throws HttpExceptionInterface
     * @throws InvalidArgumentException
     */
    public function getResource(string $type, array $filters = [], array $fields = [], array $options = []): Collection;
}
