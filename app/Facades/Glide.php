<?php

declare(strict_types=1);

namespace App\Facades;

use App\Services\GlideImageService;
use Illuminate\Support\Facades\Facade;

class Glide extends Facade
{
    /**
     * Get the registered name of the component.
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return GlideImageService::class;
    }
}
