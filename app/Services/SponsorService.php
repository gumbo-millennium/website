<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\SponsorService as SponsorServiceContract;
use App\Helpers\Arr;
use App\Models\Sponsor;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use RuntimeException;

/**
 * Simple sponsor showing system, preventing duplicate
 * sponsors on pages.
 */
class SponsorService implements SponsorServiceContract
{
    private bool $shown = false;

    private ?Sponsor $sponsor = null;

    /**
     * Returns if the current page still needs a sponsor.
     * Result might change mid-page, if a sponsor is present earlier.
     */
    public function hasSponsor(): bool
    {
        // Return false if hidden or if no Sponsor is returned
        return $this->shown === false
            && $this->querySponsor() !== null;
    }

    /**
     * Returns the sponsor for this page, if any.
     */
    public function getSponsor(): ?Sponsor
    {
        // If hidden or already shown, hide sponsor
        if ($this->shown) {
            return null;
        }

        // Check the sponsor and null if not found
        $sponsor = $this->querySponsor();
        if (! $sponsor) {
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
     * Converts a sponsor to inline SVG.
     *
     * @throws InvalidArgumentException
     */
    public function toSvg(?Sponsor $sponsor, array $attrs, string $style = 'gray'): ?HtmlString
    {
        // Check style
        if ($style !== 'gray' && $style !== 'color') {
            return null;
        }

        // Get style-based property
        $property = "logo_{$style}";

        // Return empty if not found
        if (! $sponsor || ! $sponsor->{$property}) {
            return null;
        }

        // Check cache
        $cacheKey = vsprintf('sponsor.%d-%s.%s', [
            $sponsor->id,
            substr(md5($sponsor->{$property}), 0, 16),
            substr(md5(\http_build_query($attrs)), 0, 16),
        ]);

        // Load from cache
        if (Cache::has($cacheKey)) {
            $value = Cache::get($cacheKey);

            return $value ? new HtmlString($value) : null;
        }

        try {
            // Get SVG
            $content = Storage::disk(Sponsor::LOGO_DISK)->get($sponsor->{$property});
        } catch (FileNotFoundException $exception) {
            // Handle not founds
            report(new RuntimeException(
                "Could not find image for {$sponsor->name} (Sponsor #{$sponsor->id})",
                404,
                $exception,
            ));

            // Cache null
            Cache::put($cacheKey, null, now()->addHours(6));

            // Return null
            return null;
        }

        // Build attributes
        $attributes = [''];
        foreach ($attrs as $name => $value) {
            $value = implode(' ', Arr::wrap($value));
            $value = \htmlspecialchars($value, \ENT_COMPAT | \ENT_NOQUOTES | \ENT_HTML5);
            $value = str_replace('"', '\\"', $value);
            $attributes[] = sprintf('%s="%s"', $name, $value);
        }

        // Replace SVG tag with new tag
        $content = \str_replace('<svg', sprintf('<svg%s', implode(' ', $attributes)), $content, $count);

        // Ensure we've seen an SVG
        if ($count !== 1) {
            // Cache invalid
            Cache::put($cacheKey, null, now()->addHours(6));

            // Return null
            return null;
        }

        // Assign
        Cache::put($cacheKey, $content, now()->addHours(6));

        // Return HTML string
        return new HtmlString($content);
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
        if (! $this->sponsor) {
            $this->shown = true;
        }

        // Return sponsor
        return $this->sponsor;
    }
}
