<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Facades\Sponsors;
use App\Http\Controllers\Shop\ProductController;
use App\Models\Activity;
use App\Models\NewsItem;
use App\Models\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class HomepageController extends Controller
{
    /**
     * Renders the homepage.
     *
     * @return Response
     */
    public function show(Request $request)
    {
        // Get sponsors
        $sponsors = Sponsor::query()
            ->whereAvailable()
            ->inRandomOrder()
            ->get()
            ->sortBy('name');

        // Has existing users
        $user = $request->user();

        // Get next set of events
        $nextEvents = Activity::query()
            ->whereInTheFuture()
            ->whereAvailable($user)
            ->withoutUncertainty()
            ->orderBy('start_date')
            ->withEnrollmentsFor($user)
            ->take(3)
            ->get();

        // Find the currently advertised product
        $advertisedProduct = ProductController::getAdvertisedProduct();

        // Find the news items
        $newsItems = NewsItem::query()
            ->whereAvailable()
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->take(3)
            ->get();

        // Return view
        return Response::view('content.home', [
            'sponsors' => $sponsors,
            'activities' => $nextEvents,
            'enrollments' => $nextEvents->pluck('enrollments')->flatten(),
            'newsItems' => $newsItems,
            'advertisedProduct' => $advertisedProduct,
        ]);
    }
}
