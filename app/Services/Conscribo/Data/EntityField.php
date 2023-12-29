<?php

declare(strict_types=1);

namespace App\Services\Conscribo\Data;

use App\Services\Conscribo\Enums\FieldType;
use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;

/**
 * @property-read string $fieldName
 * @property-read string $entityType
 * @property-read string $label
 * @property-read string $description
 * @property-read string $type
 * @property-read bool $required
 * @property-read bool $readOnly
 * @property-read string $sharedFieldName
 */
class EntityField extends Fluent
{
    private const DEFAULT_VALUES = [
        'fieldName' => '',
        'entityType' => '',
        'label' => '',
        'description' => '',
        'type' => null,
        'required' => false,
        'readOnly' => false,
        'sharedFieldName' => null,
    ];

    public function __construct($item = [])
    {
        if (is_array($item)) {
            $item = Arr::only(array_merge(self::DEFAULT_VALUES, $item), array_keys(self::DEFAULT_VALUES));
            $item['type'] = FieldType::fromApiType($item['type']);
        }

        parent::__construct($item);
    }
}
