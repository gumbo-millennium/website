<?php

declare(strict_types=1);

namespace App\Facades;

use App\Contracts\SponsorService;
use App\Models\Sponsor;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool hasSponsor()
 * @method static null|Sponsor getSponsor()
 * @method static void hideSponsor()
 * @see \App\Contracts\SponsorService
 * @see \App\Services\SponsorService
 */
class Sponsors extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return SponsorService::class;
    }
}
