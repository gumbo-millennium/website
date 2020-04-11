<?php

declare(strict_types=1);

namespace App\Observers;

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
        // TODO
    }
}
