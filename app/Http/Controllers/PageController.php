<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\SponsorService;
use App\Http\Controllers\Shop\ProductController;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\Page;
use App\Models\Sponsor;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Cache\Repository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class PageController extends Controller
{
    private Repository $cache;

    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Renders the homepage.
     *
     * @return Response
     */
    public function homepage(SponsorService $sponsorService, Request $request)
    {
        // Get sponsors
        $homeSponsors = Sponsor::query()
            ->whereAvailable()
            ->inRandomOrder()
            ->take(4)
            ->get();

        // Mark 4 sponsors as shown
        $homeSponsors->each->increment('view_count');

        // Hide sponsors on the page if some are present
        ($homeSponsors->count() === 4) and $sponsorService->hideSponsor();

        // Has existing users
        $user = $request->user();
        $member = $user && $user->is_member ? 'member' : 'guest';

        // Get next set of events
        // phpcs:ignore SlevomatCodingStandard.Functions.RequireArrowFunction.RequiredArrowFunction
        $nextEvents = $this->cache->remember("home.events.{$member}", now()->addMinutes(10), static function () {
            return Activity::query()
                ->whereAvailable()
                ->where('start_date', '>', now())
                ->whereNull('cancelled_at')
                ->orderBy('start_date')
                ->take(2)
                ->get();
        });

        // Get enrollments
        $enrollments = [];
        if ($request->user() && $nextEvents) {
            $enrollments = Enrollment::query()
                ->whereUserId($request->user()->id)
                ->where('activity_id', 'in', $nextEvents->pluck('id'))
                ->orderBy('created_at', 'asc')
                ->get()
                ->keyBy('activity_id');
        }

        $advertisedProduct = ProductController::getAdvertisedProduct();

        // Return view
        return response()
            ->view('content.home.layout', [
                'homeSponsors' => $homeSponsors,
                'nextEvents' => $nextEvents,
                'enrollments' => $enrollments,
                'advertisedProduct' => $advertisedProduct,
            ])
            ->setPublic()
            ->setMaxAge(60 * 15); // Cache for 15 min max
    }

    /**
     * Handles fallback routes.
     *
     * @return Response
     */
    public function fallback(Request $request)
    {
        try {
            // Rnder the page
            return $this->render(null, trim($request->path(), '/\\'));
        } catch (ModelNotFoundException $pageNotFound) {
            // Try to see if it might be a redirect.
            return App::call(RedirectController::class . '@fallback');
        }
    }

    /**
     * Group overview page.
     *
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    public function group(string $group)
    {
        $pages = Page::where(compact('group'))->get();
        $lastModified = $pages->max('updated_at');
        $page = Page::where([
            'hidden' => false,
            'group' => null,
            'slug' => $group,
        ])->first();

        return response()
            ->view('content.group', compact('pages', 'page', 'group'))
            ->setLastModified($lastModified)
            ->setMaxAge(now()->addHours(6)->diffInSeconds())
            ->setSharedMaxAge(now()->addHour()->diffInSeconds())
            ->setPublic();
    }

    /**
     * Group detail page.
     *
     * @return App\Http\Controllers\Response
     * @throws HttpResponseException
     */
    public function groupPage(string $group, string $slug)
    {
        return $this->render($group, $slug);
    }

    /**
     * Renders a single page, if possible.
     *
     * @return Response
     */
    protected function render(?string $group, string $slug)
    {
        // Get page
        $page = Page::query()
            ->whereGroup($group)
            ->whereSlug($slug)
            ->whereHidden(false)
            ->firstOrFail();

        // Set SEO
        SEOTools::setCanonical($page->url);
        SEOTools::setTitle($page->title);
        SEOTools::setDescription($page->description);
        SEOTools::addImages([$page->image->url('social')]);

        // Show view
        return response()
            ->view('content.page', compact('page'))
            ->setLastModified($page->updated_at)
            ->setMaxAge(now()->addHours(6)->diffInSeconds())
            ->setSharedMaxAge(now()->addHour()->diffInSeconds())
            ->setPublic();
    }
}
