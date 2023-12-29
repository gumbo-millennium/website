<?php

declare(strict_types=1);

namespace App\Services\Conscribo;

use App\Services\Conscribo\Contracts\Client as ClientContract;
use App\Services\Conscribo\Data\EntityCollection;
use App\Services\Conscribo\Data\EntityType;
use App\Services\Conscribo\Enums\FilterOperator;
use DateTimeInterface;
use Illuminate\Support\Arr;
use LogicException;

class ResourceQuery
{
    use Concerns\MapsEntityResults;

    public const VALID_OPERATORS = [
        '=',
        '~',
        '!~',
        '|=',
        '+',
        '-',
        '><',
        '>=',
        '<=',
        'in',
        'all',
    ];

    private array $filters = [];

    public function __construct(
        readonly protected ClientContract $client,
        readonly protected EntityType $resource,
    ) {
        //
    }

    public function where(string $property, mixed $operator, mixed $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        if (! in_array($operator, self::VALID_OPERATORS, true)) {
            throw new LogicException("Invalid operator: {$operator}");
        }

        $this->filters[] = [
            'fieldName' => $property,
            'operator' => "{$operator}",
            'value' => $value,
        ];

        return $this;
    }

    public function whereBefore(string $property, DateTimeInterface $value): self
    {
        return $this->where($property, FilterOperator::DateBefore, [
            'end' => $value,
        ]);
    }

    public function whereAfter(string $property, DateTimeInterface $value): self
    {
        return $this->where($property, FilterOperator::DateAfter, [
            'start' => $value,
        ]);
    }

    public function whereBetween(string $property, DateTimeInterface $start, DateTimeInterface $end): self
    {
        return $this->where($property, FilterOperator::DateBetween, [
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function execute(?array $columns = null): EntityCollection
    {
        if (! $this->resource) {
            throw new LogicException('No resource specified.');
        }

        $availableColumns = $this->resource->fields->keys();

        $columns ??= $availableColumns;

        $invalidColumns = $availableColumns->diff($columns);
        if ($invalidColumns->isNotEmpty()) {
            throw new LogicException(
                sprintf(
                    'Invalid columns specified: %s',
                    $invalidColumns->implode(', '),
                ),
            );
        }

        $params = array_filter([
            'entityType' => $this->getResourceName(),
            'requestedFields' => $columns,
            'filters' => $this->getFilters(),
        ]);

        $result = $this->client->request('listRelations', $params);

        return $this->mapEntityResults($this->resource->fields, Arr::get($result, 'relations', []));
    }

    public function getResourceName(): string
    {
        return $this->resource->typeName;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }
}
