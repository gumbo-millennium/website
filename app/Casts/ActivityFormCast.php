<?php

declare(strict_types=1);

namespace App\Casts;

use App\Nova\Flexible\Presets\ActivityForm;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Whitecube\NovaFlexibleContent\Value\FlexibleCast;

class ActivityFormCast extends FlexibleCast implements CastsAttributes
{
    public function getLayoutMapping()
    {
        // Return empty collection if Nova is disabled
        if (! Config::get('services.features.enable-nova')) {
            return [];
        }

        // Map layouts with keys
        return Collection::make(ActivityForm::LAYOUTS)
            ->mapWithKeys(fn ($item) => [(new $item())->name() => $item])
            ->all();
    }
}
