<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\SponsorService;
use App\Models\Activity;
use App\Models\Page;
use App\Models\Role;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class LustrumController extends Controller
{
    private const SHOP_CACHE = 'lustrum.merchandise';

    public function index(Request $request, SponsorService $sponsorService): HttpResponse
    {
        // Ensure all links are egress
        URL::forceRootUrl(Config::get('app.url'));

        // Disable sponsors
        $sponsorService->hideSponsor();

        // Get the page
        $page = $this->getPage();

        // Assign SEO data
        if ($page) {
            SEOMeta::setTitle("{$page->title} - Gumbo Millennium");
            SEOMeta::setDescription($page->summary);
        }

        // Assign canonical link, to prevent duplicate content
        if (! App::isLocal()) {
            $lustrumRoot = sprintf('https://%s', Config::get('gumbo.lustrum-domains')[0]);
            SEOMeta::setCanonical($lustrumRoot);
        }

        // Get merchandise
        $merchandise = $this->getMerchandise();

        return Response::view('minisite.lustrum', [
            'lustrumNav' => true,
            'merchandise' => $merchandise,
            'activities' => $this->getActivities(),
            'page' => $page,
        ]);
    }

    /**
     * Ensures all other minisite pages throw a 404, but
     * with the right nav.
     */
    public function other(Request $request): HttpResponse
    {
        // Ensure all links are egress
        URL::forceRootUrl(Config::get('app.url'));

        return Response::view('errors.404', [
            'lustrumNav' => true,
        ], HttpResponse::HTTP_NOT_FOUND);
    }

    private function getPage(): ?Page
    {
        return Page::findBySlug('lustrum');
    }

    private function getActivities(): Collection
    {
        try {
            $activityHost = Role::findByName('lucie');
            assert($activityHost instanceof Role);

            return Activity::query()
                ->whereHas('role', fn (Builder $query) => $query->where('role_id', $activityHost->getKey()))
                ->whereAvailable()
                ->where('start_date', '>', now())
                ->whereNull('cancelled_at')
                ->orderBy('start_date')
                ->get()
                ->toBase();
        } catch (RoleDoesNotExist $roleNotFoundError) {
            // Present an empty array of activities
            return Collection::make();
        }
    }

    private function getMerchandise(): Collection
    {
        $shopItemIds = Cache::get(self::SHOP_CACHE, null);

        // Found no items
        if ($shopItemIds === []) {
            return Collection::make();
        }

        // Found items
        if ($shopItemIds !== null) {
            return Product::query()
                ->whereIn('id', $shopItemIds)
                ->where('visible', true)
                ->with('variants')
                ->get()
                ->toBase();
        }

        // Find the category that contains these products
        $shopCategory = Category::findBySlug('lustrum');

        // No category found, cache that no items were saved
        if (! $shopCategory || ! $shopCategory->visible) {
            Cache::put(self::SHOP_CACHE, [], Date::now()->addMinutes(5));

            return Collection::make();
        }

        // Find the products
        $shopItems = $shopCategory->products()
            ->where('visible', true)
            ->with('variants')
            ->get()
            ->toBase();

        // Cache the items
        Cache::put(self::SHOP_CACHE, $shopItems->pluck('id')->toArray(), Date::now()->addMinutes(30));

        // Done 😃
        return $shopItems->toBase();
    }
}
