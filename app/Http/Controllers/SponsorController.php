<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\SponsorService;
use App\Models\Sponsor;
use App\Models\SponsorClick;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;

class SponsorController extends Controller
{
    /**
     * Return index of sponsors.
     *
     * @return string
     */
    public function index()
    {
        // Get sponsors with and without
        $baseQuery = Sponsor::whereAvailable();

        // Get branded and simple sponsors
        $brandedSponsors = (clone $baseQuery)->where('has_page', 1)->get();
        $simpleSponsors = (clone $baseQuery)->where('has_page', 0)->get();

        // Set SEO
        SEOMeta::setTitle('Sponsoren');
        SEOMeta::setCanonical(route('sponsors.index'));

        // Build response
        return Response::view('sponsors.index', [
            'branded' => $brandedSponsors,
            'simple' => $simpleSponsors,
        ])->setLastModified(Carbon::parse((clone $baseQuery)->max('updated_at')));
    }

    /**
     * Returns single sponsor, if they have a detail page.
     *
     * @return Response
     * @throws HttpResponseException
     */
    public function show(SponsorService $service, string $sponsor)
    {
        // Find sponsor
        $sponsor = Sponsor::withTrashed()->whereSlug($sponsor)->first();

        // Hide if none found
        if (! $sponsor || ! $sponsor->has_page) {
            abort(404);
        }

        // Disable sponsor
        $service->hideSponsor();

        // Return 410 gone if it used to exist
        if ($sponsor->trashed()) {
            abort(410);
        }

        // 402 if expired or invalid
        if (! $sponsor->is_active) {
            abort(402);
        }

        // Set SEO
        SEOMeta::setTitle("{$sponsor->contents_title} - {$sponsor->name} - Sponsoren");
        SEOMeta::setCanonical(route('sponsors.show', compact('sponsor')));

        // Return view
        return Response::view('sponsors.show', [
            'sponsor' => $sponsor,
        ]);
    }

    /**
     * Redirects a user to the sponsor, if found.
     *
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
        if (! $sponsor) {
            abort(404);
        }

        // 410 if removed
        if ($sponsor->trashed()) {
            abort(410);
        }

        // 402 if expired or invalid
        if (! $sponsor->is_active) {
            abort(402);
        }

        // Raise count
        SponsorClick::addClick($sponsor);

        // Redirect user
        return Response::redirectTo($sponsor->url)
            ->header('Referrer-Policy', 'no-referrer, strict-origin')
            ->setLastModified($sponsor->updated_at);
    }
}
