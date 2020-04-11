<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\SponsorService;
use App\Models\Sponsor;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Support\Carbon;

class SponsorController extends Controller
{
    /**
     * Return index of sponsors
     * @param Request $request
     * @return string
     */
    public function index()
    {
        // Get sponsors with and without
        $baseQuery = Sponsor::whereAvailable();

        // Get branded and simple sponsors
        $brandedSponsors = (clone $baseQuery)->where('has_page', '1')->get();
        $simpleSponsors = (clone $baseQuery)->where('has_page', '0')->get();

        // Set SEO
        SEOMeta::setTitle('Sponsoren');
        SEOMeta::setCanonical(route('sponsors.index'));

        // Build response
        return \response()
            ->view('sponsors.index', [
                'branded' => $brandedSponsors,
                'simple' => $simpleSponsors
            ])
            ->setCache([
                'public' => true,
                'max_age' => 60 * 15,
                's_maxage' => 60 * 5,
                'last_modified' => Carbon::parse((clone $baseQuery)->max('updated_at'))
            ]);
    }

    /**
     * Returns single sponsor, if they have a detail page
     * @param SponsorService $service
     * @param string $sponsor
     * @return Response
     * @throws HttpResponseException
     */
    public function show(SponsorService $service, string $sponsor)
    {
        // Find sponsor
        $sponsor = Sponsor::withTrashed()->whereSlug($sponsor)->first();

        // Hide if none found
        if (!$sponsor || !$sponsor->has_page) {
            abort(404);
        }

        // Disable sponsor
        $service->hideSponsor();

        // Return 410 gone if it used to exist
        if ($sponsor->trashed()) {
            abort(410);
        }

        // 402 if expired or invalid
        if (!$sponsor->is_active) {
            abort(402);
        }

        // Return view
        return \response()
            ->view('sponsors.show', compact('sponsor'))
            ->setPublic();
    }

    /**
     * Redirects a user to the sponsor, if found.
     * @param string $sponsor
     * @return RedirectResponse
     * @throws HttpResponseException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function redirect(string $sponsor)
    {
        // Find sponsor
        $sponsor = Sponsor::withTrashed()->whereSlug($sponsor)->first();

        // Hide if none found
        if (!$sponsor) {
            abort(404);
        }

        // 410 if removed
        if ($sponsor->trashed()) {
            abort(410);
        }

        // 402 if expired or invalid
        if (!$sponsor->is_active) {
            abort(402);
        }

        // Raise count
        DB::table('sponsors')->where('id', $sponsor->id)->increment('click_count');

        // Redirect user
        return \redirect()
            ->away($sponsor->url)
            ->header('Referrer-Policy', 'no-referrer, strict-origin')
            ->setPublic()
            ->setCache([
                'public' => true,
                'last_modified' => $sponsor->updated_at,
                'max_age' => 60 * 30,
                's_maxage' => 60 * 20,
            ]);
    }
}
