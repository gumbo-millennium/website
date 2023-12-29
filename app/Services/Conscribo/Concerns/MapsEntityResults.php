<?php

declare(strict_types=1);

namespace App\Services\Conscribo\Concerns;

use App\Services\Conscribo\Data\EntityCollection;
use App\Services\Conscribo\Data\EntityFieldCollection;
use App\Services\Conscribo\Enums\FieldType;
use Illuminate\Support\Facades\Date;

trait MapsEntityResults
{
    protected function mapEntityResults(EntityFieldCollection $fields, array $result): EntityCollection
    {
        $fieldTypeMap = $fields->pluck('type', 'fieldName');

        foreach ($result as &$row) {
            foreach ($row as $fieldName => &$value) {
                $value = match($fieldTypeMap->get($fieldName)) {
                    FieldType::Date => Date::parse($value),
                    FieldType::Checkbox => (bool) $value,
                    FieldType::Integer => intval($value, 10),
                    FieldType::Number => (float) $value,
                    FieldType::Amount => money_value(str_replace(',', '.', $value)),
                    default => $value,
                };
            }
        }

        return EntityCollection::apiMake($result);
    }
}
