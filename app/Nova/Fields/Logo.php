<?php

declare(strict_types=1);

namespace App\Nova\Fields;

use App\Models\Sponsor;
use Laravel\Nova\Fields\Image;

class Logo extends Image
{
    /**
     * Creates a logo
     * @return static
     */
    public static function make(...$arguments)
    {
        return parent::make(...$arguments)
            ->acceptedTypes('svg')
            ->deletable(false)
            ->disableDownload()
            ->disk(Sponsor::LOGO_DISK)
            ->path(Sponsor::LOGO_PATH)
            ->prunable(true);
    }
}
