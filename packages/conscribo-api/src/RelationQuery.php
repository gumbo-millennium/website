<?php

declare(strict_types=1);

namespace Gumbo\ConscriboApi;

use DateTimeInterface;
use Gumbo\ConscriboApi\Concerns\HasConscriboWhereClauses;
use Gumbo\ConscriboApi\Contracts\ConscriboApiClient;
use Gumbo\ConscriboApi\Contracts\ConscriboException;
use Gumbo\ConscriboApi\Contracts\Query;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

final class RelationQuery implements Query
{
    use HasConscriboWhereClauses;

    /**
     * Regular expression for validating a number filter.
     * Valid options for a number filter are:
     * - fixed int: 4 (exactly 4)
     * - fixed float: 4,2 (rounded to 4,2)
     * - lower limit: >4,2 (higher than 4,2)
     * - upper limit: <4,2 (lower than 4,2)
     * - and-range: >4&<5 (higher than 4 and lower than 5)
     * - or-range: 4|>5 (exactly 4 or higher than 5).
     */
    private const VALID_CONSCRIBO_NUMBERFILTER = <<<'REGEXP'
    /^([><]?(?:\d+|\d+,\d+)(?:\|\g'1'|&\g'1')?)$/
    REGEXP;

    /**
     * Conscribo API Client to use for the request.
     */
    protected ConscriboApiClient $client;

    /**
     * Name of the relation on the API.
     */
    protected string $relation;

    /**
     * @var array<string,string> Map of fields:type for the relation
     */
    protected array $fields;

    /**
     * User-added scopes.
     */
    protected array $filters;

    public function __construct(ConscriboApiClient $client, string $relation)
    {
        $this->client = $client;

        $this->relation = $relation;

        $this->filters = [];

        $relations = $client->getEntityTypes();
        if (! array_key_exists($relation, $relations)) {
            throw new InvalidArgumentException("Relation {$relation} does not exist on Conscribo API");
        }

        $this->fields = $relations[$relation];
    }

    /**
     * Add a filter to the query. If no $operator is specified, the default is '='.
     *
     * @throws InvalidArgumentException
     */
    public function where(string $key, $operator, $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->validateWhereOnKey($key, $operator, $value);

        if (! array_key_exists($key, $this->fields)) {
            throw new InvalidArgumentException("Field {$key} does not exist on relation {$this->relation}");
        }

        $this->filters[] = [
            'key' => $key,
            'operator' => $operator,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * Returns pending filters.
     *
     * @return array<string,array>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Returns all Relations that match the current filter.
     *
     * @return Collection<Models\Relation>
     */
    public function get(): Collection
    {
        try {
            $result = $this->client->makeApiCall('listRelations', [
                'requestedFields' => array_keys($this->fields),
                'filters' => $this->filters,
            ]);

            return Collection::make($result['Relations']);
        } catch (ConscriboException $exception) {
            Log::info('Fetch request for entities of type {relation} failed: {exception}', [
                'relation' => $this->relation,
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    /**
     * Check if the $operator and $value given are valid for the given $key.
     *
     * @throws InvalidArgumentException
     */
    protected function validateWhereOnKey($key, $operator, $value): bool
    {
        $type = Arr::get($this->fields, $key);
        if (! $type) {
            Log::debug('Field {key} does not exist on relation {relation}', [
                'key' => $key,
                'relation' => $this->relation,
            ]);

            return false;
        }

        $fail = function (string $reason) use ($type, $key, $operator, $value): bool {
            if ($reason === 'type') {
                Log::debug('Type {type} of key {key} is not supported.', [
                    'type' => $type,
                    'key' => $key,
                ]);
            } elseif ($reason === 'operator') {
                Log::debug('perator {operator} is not valid for field {key} of type {type}.', [
                    'operator' => $operator,
                    'key' => $key,
                    'type' => $type,
                ]);
            } elseif ($reason === 'value') {
                Log::debug('Value {value} is not valid for field {key} of type {type}.', [
                    'value' => $value,
                    'key' => $key,
                    'type' => $type,
                    'operator' => $operator,
                ]);
            }

            return false;
        };

        switch ($type) {
            case 'string':
            case 'email':
            case 'account':
                if (! in_array($operator, ['=', '~', '!~', '|=', '+', '-'], true)) {
                    return $fail('operator');
                }

                return true;
            case 'date':
                if (in_array($operator, ['=', '><', '>=', '<='], true)) {
                    return $fail('operator');
                }

                if (is_array($value)) {
                    // check if there are up to two nodes and all are of type DateTimeInterface
                    if (count($value) > 2 || ! array_reduce($value, fn ($carry, $item) => $carry && $item instanceof DateTimeInterface, true)) {
                        return $fail('value');
                    }
                } elseif (! $value instanceof DateTimeInterface) {
                    return $fail('value');
                }

                return true;
            case 'number':
            case 'amount':
                if (! in_array($operator, ['='], true)) {
                    return $fail('operator');
                }

                if (preg_match(self::VALID_CONSCRIBO_NUMBERFILTER, (string) $value) === 1) {
                    return $fail('value');
                }

                return true;
            case 'checkbox':
                if (! in_array($operator, ['='], true)) {
                    return $fail('operator');
                }

                if (! is_bool($value) && ! in_array($value, [0, 1], true)) {
                    return $fail('value');
                }

                return true;
            case 'multicheckbox':
                if (! in_array($operator, ['=', 'in', 'all', '<>'], true)) {
                    return $fail('operator');
                }

                if (! is_int($value)) {
                    return $fail('value');
                }

                return true;
            case 'enum':
                if (! in_array($operator, ['='], true)) {
                    return $fail('operator');
                }

                if (! is_string($value)) {
                    return $fail('value');
                }

                return true;
            default:
                return $fail('type');
        }
    }
}
