<?php

declare(strict_types=1);

namespace Gumbo\ConscriboApi;

use Gumbo\ConscriboApi\Contracts\ConscriboApiClient;
use Gumbo\ConscriboApi\Contracts\ConscriboException;
use Gumbo\ConscriboApi\Contracts\Query;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

final class GroupQuery implements Query
{
    /**
     * Conscribo API Client to use for the request.
     */
    protected ConscriboApiClient $client;

    /**
     * Name of the relation on the API.
     */
    protected ?string $groupId;

    public function __construct(ConscriboApiClient $client)
    {
        $this->client = $client;

        $this->groupId = null;
    }

    /**
     * Return only the given group ID.
     */
    public function whereGroupId(int $groupId): self
    {
        $this->groupId = $groupId;

        return $this;
    }

    /**
     * Returns all Relations that match the current filter.
     *
     * @return Collection<Models\Group>
     */
    public function get(): Collection
    {
        try {
            $result = $this->client->makeApiCall('listEntityGroups', array_filter([
                'groupId' => $this->groupId,
            ]));

            return Collection::make(Arr::get('entityGroups', $result, []))
                ->map(fn ($row) => new Models\Group($row))
                ->values();
        } catch (ConscriboException $exception) {
            Log::info('Fetch request for groups (with group ID {groupid} failed: {exception}', [
                'groupid' => $this->groupId,
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }
}
