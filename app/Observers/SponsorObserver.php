<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\ImageJobs\CompressSvg;
use App\Jobs\ImageJobs\RemoveImageColors;
use App\Jobs\ImageJobs\ValidateSvg;
use App\Models\Sponsor;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\Storage;

class SponsorObserver
{
    private const TRACKING_QUERY_PARAMS = [
        "/^utm(_[a-zA-Z]*)?/",
        "/^ga_[a-zA-Z_]+/",
        "/^yclid/",
        "/^_openstat/",
        "/^fb_action_ids/",
        "/^fb_action_types/",
        "/^fb_source/",
        "/^fb_ref/",
        "/^fbclid/",
        "/^action_object_map/",
        "/^action_type_map/",
        "/^action_ref_map/",
        "/^gs_l/",
        "/^mkt_tok/",
        "/^hmb_campaign/",
        "/^hmb_medium/",
        "/^hmb_source/",
        "/^ref[\\_]?/",
        "/^gclid/",
        "/^otm_[a-zA-Z_]*/",
        "/^cmpid/",
        "/^os_ehash/",
        "/^_ga/",
        "/^__twitter_impression/",
        "/^wt_?z?mc/",
        "/^wtrid/",
        "/^[a-zA-Z]?mc/",
        "/^dclid/",
        "/^x/",
        "/^spm/",
        "/^vn(_[a-zA-Z]*)+/",
    ];

    public function saving(Sponsor $sponsor): void
    {
        // Validate logos
        $logos = ['logo_gray', 'logo_color'];
        foreach ($logos as $logo) {
            if (
                $sponsor->wasChanged($logo) &&
                !Storage::disk(Sponsor::LOGO_DISK)->exists($sponsor->$logo)
            ) {
                $sponsor->$logo = null;
            }
        }

        // Clean URL from trackers
        if ($sponsor->wasChanged('url')) {
            // Get URI
            $uri = new Uri($sponsor->url);

            // Get a list of query params
            parse_str($uri->getQuery(), $oldParams);

            // Iterate all nodes
            foreach (array_keys($oldParams) as $key) {
                // Match against all params
                foreach (self::TRACKING_QUERY_PARAMS as $regex) {
                    // If it matches against the regex, skip it
                    if (preg_match($regex, $key)) {
                        // Remove the value
                        $uri = Uri::withoutQueryValue($uri, $key);

                        // Skip the query-loop
                        continue 2;
                    }
                }
            }

            // Create new URL
            $sponsor->url = (string) $uri;
            $sponsor->withoutEvents(static fn () => $sponsor->save(['url']));
        }
    }

    /**
     * Dispatches an SVG update if the logos were changed, and remove trackers from the URLs
     * @param Sponsor $sponsor
     * @return void
     */
    public function saved(Sponsor $sponsor): void
    {
        // Noop when in CLI (prevents infinite loops)
        if (app()->runningInConsole()) {
            return;
        }


        // Optimize logo and add grayscale
        if ($sponsor->wasChanged('logo_gray')) {
            ValidateSvg::withChain([
                new CompressSvg($sponsor, 'logo_gray'),
                new RemoveImageColors($sponsor, 'logo_gray'),
                new CompressSvg($sponsor, 'logo_gray'),
            ])->dispatch($sponsor, 'logo_gray');
        }

        // Optimize logo
        if ($sponsor->wasChanged('logo_color')) {
            ValidateSvg::withChain([
                new CompressSvg($sponsor, 'logo_color'),
            ])->dispatch($sponsor, 'logo_color');
        }
    }
}
