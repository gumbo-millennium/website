<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\MustAcceptJson;
use App\Http\Resources\Api\Minisite\SitePageResource;
use App\Http\Resources\Api\Minisite\SitePageSitemapResource;
use App\Http\Resources\Api\Minisite\SiteResource;
use App\Models\Minisite\Site;
use App\Models\Minisite\SitePage;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class MinisiteController extends Controller
{
    public function __construct()
    {
        $this->middleware(MustAcceptJson::class);
    }

    public function config(string $domain)
    {
        return SiteResource::make(
            Site::whereDomain($domain)->firstOrFail(),
        );
    }

    /**
     * Returns all visible pages, for the sitemap.
     */
    public function sitemap(string $domain): ResourceCollection
    {
        $site = Site::whereDomain($domain)->firstOrFail();
        abort_unless($site, Response::HTTP_NOT_FOUND);

        if (! $site->enabled) {
            return SitePageResource::collection([]);
        }

        $pages = SitePage::whereSite($site)
            ->where('visible', true)
            ->with('site')
            ->get([
                'id',
                'slug',
                'site_id',
                'updated_at',
            ]);

        return SitePageSitemapResource::collection($pages);
    }

    /**
     * Shows contents of a given page.
     */
    public function showPage(string $domain, string $page): SitePageResource
    {
        return SitePageResource::make(
            SitePage::whereSite($domain)
                ->where('slug', $page)
                ->with('site')
                ->firstOrFail(),
        );
    }
}
