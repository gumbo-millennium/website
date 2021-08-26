<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Sponsor;

/**
 * Derp.
 */
interface SponsorService
{
    /**
     * Returns if the current page still needs a sponsor.
     * Result might change mid-page, if a sponsor is present earlier.
     */
    public function hasSponsor(): bool;

    /**
     * Returns the sponsor for this page, if any.
     */
    public function getSponsor(): ?Sponsor;

    /**
     * Indicates that this page should not render a sponsor. Should
     * be called before the views are rendered.
     */
    public function hideSponsor(): void;
}
