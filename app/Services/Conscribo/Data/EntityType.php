<?php

declare(strict_types=1);

namespace App\Services\Conscribo\Data;

use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;

/**
 * @property-read string $typeName
 * @property-read string $langDeterminer
 * @property-read string $langSingular
 * @property-read string $langPlural
 * @property-read EntityFieldCollection $fields
 */
class EntityType extends Fluent
{
    private const DEFAULT_VALUES = [
        'typeName' => '',
        'langDeterminer' => '',
        'langSingular' => '',
        'langPlural' => '',
    ];

    public function __construct(array $item = [])
    {
        if (is_array($item)) {
            $item = Arr::only(array_merge(self::DEFAULT_VALUES, $item), array_keys(self::DEFAULT_VALUES));
        }

        parent::__construct($item);
    }
}
