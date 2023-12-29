<?php

declare(strict_types=1);

namespace App\Services\Conscribo\Data;

use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;

/**
 * @property-read int $entityId
 * @property-read string $entityType
 */
class RelationIdent extends Fluent
{
    private const DEFAULT_VALUES = [
        'entityId' => '',
        'entityType' => '',
    ];

    public function __construct(array $item = [])
    {
        if (is_array($item)) {
            $item = Arr::only(array_merge(self::DEFAULT_VALUES, $item), array_keys(self::DEFAULT_VALUES));
        }

        parent::__construct($item);
    }
}
