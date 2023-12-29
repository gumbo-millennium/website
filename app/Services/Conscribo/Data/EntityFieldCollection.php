<?php

declare(strict_types=1);

namespace App\Services\Conscribo\Data;

use App\Services\Conscribo\Contracts\ApiMakeable;
use Illuminate\Support\Collection;

/**
 * @extends Collection<string,EntityField>
 */
class EntityFieldCollection extends Collection implements ApiMakeable
{
    public static function apiMake(array $items): static
    {
        return new static(
            Collection::make($items)
                ->keyBy('fieldName')
                ->map(fn ($row) => new EntityField($row)),
        );
    }
}
