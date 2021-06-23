<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\SponsorService;
use Closure;

/**
 * Makes sure these routes don't have a sponsor on the page.
 */
class HideSponsor
{
    private SponsorService $sponsorService;

    /**
     * Requires a sponsor.
     *
     * @return void
     */
    public function __construct(SponsorService $sponsorService)
    {
        $this->sponsorService = $sponsorService;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function handle($request, Closure $next)
    {
        // Disable sponsor
        $this->sponsorService->hideSponsor();

        // Forward call
        return $next($request);
    }
}
