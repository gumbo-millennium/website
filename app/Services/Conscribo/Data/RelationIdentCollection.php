<?php

declare(strict_types=1);

namespace App\Services\Conscribo\Data;

use App\Services\Conscribo\Contracts\ApiMakeable;
use Illuminate\Support\Collection;

/**
 * @extends Collection<string,RelationIdent>
 */
class RelationIdentCollection extends Collection implements ApiMakeable
{
    public static function apiMake(array $items): static
    {
        return new static(
            Collection::make($items)
                ->map(fn ($row) => new RelationIdent($row)),
        );
    }
}
