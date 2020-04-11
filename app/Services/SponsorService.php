<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\SponsorService as SponsorServiceContract;
use App\Models\Sponsor;
use Illuminate\Support\Facades\DB;

/**
 * Simple sponsor showing system, preventing duplicate
 * sponsors on pages
 */
class SponsorService implements SponsorServiceContract
{
    private bool $shown = false;
    private ?Sponsor $sponsor = null;

    private function querySponsor(): ?Sponsor
    {
        // Don't query if hidden
        if ($this->shown) {
            return null;
        }

        // Return sponsor if set
        if ($this->sponsor) {
            return $this->sponsor;
        }

        // Get sponsor from DB
        $this->sponsor = Sponsor::query()
            ->whereAvailable()
            ->inRandomOrder()
            ->limit(1)
            ->get()
            ->first();

        // Mark as hidden if no sponsor is available
        if (!$this->sponsor) {
            $this->shown = true;
        }

        // Return sponsor
        return $this->sponsor;
    }

    /**
     * Returns if the current page still needs a sponsor.
     * Result might change mid-page, if a sponsor is present earlier.
     * @return bool
     */
    public function hasSponsor(): bool
    {
        // Return false if hidden or if no Sponsor is returned
        return $this->shown === false
            && $this->querySponsor() !== null;
    }

    /**
     * Returns the sponsor for this page, if any.
     * @return null|Sponsor
     */
    public function getSponsor(): ?Sponsor
    {
        // If hidden or already shown, hide sponsor
        if ($this->shown) {
            return null;
        }

        // Check the sponsor and null if not found
        $sponsor = $this->querySponsor();
        if (!$sponsor) {
            return null;
        }

        // Mark as shown
        $this->shown = true;

        // Increment view count
        DB::table('sponsors')->where('id', $sponsor->id)->increment('view_count');

        // Return sponsor
        return $sponsor;
    }

    /**
     * Indicates that this page should not render a sponsor. Should
     * be called before the views are rendered.
     */
    public function hideSponsor(): void
    {
        // Just flag the sponsor as shown
        $this->shown = true;
    }
}
