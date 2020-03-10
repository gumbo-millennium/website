<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\OptimizeUserSvg;
use App\Models\Sponsor;

class SponsorObserver
{
    /**
     * Dispatches an SVG update if the logos were changed
     * @param Sponsor $sponsor
     * @return void
     */
    public function saved(Sponsor $sponsor): void
    {
        if ($sponsor->wasChanged('logo_gray')) {
            OptimizeUserSvg::dispatch($sponsor->logo_gray, OptimizeUserSvg::TARGET_MONOTONE);
        }

        if ($sponsor->wasChanged('logo_color')) {
            OptimizeUserSvg::dispatch($sponsor->logo_color, OptimizeUserSvg::TARGET_FULL_COLOR);
        }
    }
}
