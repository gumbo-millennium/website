<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\SponsorService as SponsorServiceContract;
use App\Models\Sponsor;

/**
 * Derp
 */
class SponsorService implements SponsorServiceContract
{
    /**
     * Returns if the current page still needs a sponsor.
     * Result might change mid-page, if a sponsor is present earlier.
     * @return bool
     */
    public function hasSponsor(): bool
    {
        // TODO
        return false;
    }

    /**
     * Returns the sponsor for this page, if any.
     * @return null|Sponsor
     */
    public function getSponsor(): ?Sponsor
    {
        // TODO
        return null;
    }

    /**
     * Indicates that this page should not render a sponsor. Should
     * be called before the views are rendered.
     */
    public function hideSponsor(): void
    {
        // TODO
    }
}
