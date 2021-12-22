<?php

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Whitecube\NovaFlexibleContent\Value\FlexibleCast as BaseFlexibleCast;

class FlexibleCast extends BaseFlexibleCast implements CastsAttributes
{
    public function set($model, string $key, $value, array $attributes)
    {
        return $value === null ? null : json_encode($value);
    }
}
