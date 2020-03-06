<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\NewsItem;
use App\Models\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravelium\Sitemap\Sitemap;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

/**
 * Generates sitemaps for activities,
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class SitemapController extends Controller
{
    private const SKIPPED_PAGES = [
        'word-lid'
    ];

    /**
     * Make sure the request has a valid type before sending it
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        // Check for XML support
        abort_unless(
            $request->accepts('text/xml'),
            Response::HTTP_NOT_ACCEPTABLE,
            'You need to be able to understand XML sitemaps'
        );
    }

    /**
     * Present index sitemap on homepage
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        // Reject if the user can't handle XML
        if (!$request->accepts('text/xml')) {
            return new NotAcceptableHttpException(
                'Sitemap is only available as XML, but you don\'t seem to want that.'
            );
        }

        // Get new sitemap
        $map = app('sitemap');

        // set cache key (string), duration in minutes (Carbon|Datetime|int), turn on/off (boolean)
        // by default cache is disabled
        $map->setCache('laravel.sitemap', now()->addHour());

        // Create new map if not cached or debugging
        if (!$map->isCached() || config('app.debug', false)) {
            $this->buildSitemap($map);
        }

        return $map->render('xml');
    }

    private function buildSitemap(Sitemap &$sitemap)
    {
        // Most important page, duh
        $sitemap->add(route('home'), null, '1.0', 'daily');

        // Add routes
        $this->addModelRoute($sitemap, 'activity', 'activity', Activity::whereAvailable());
        $this->addModelRoute($sitemap, 'news', 'news', NewsItem::whereAvailable());

        // Add other pages
        foreach (Page::cursor() as $page) {
            if (in_array($page->slug, self::SKIPPED_PAGES)) {
                continue;
            }

            $pageUrl = $page->group ? route('group.show', $page->only('group', 'slug')) : url("/{$page->slug}");
            $sitemap->add($pageUrl, $page->updated_at, $page->group ? '0.5' : '0.7', 'weekly');
        }
    }

    private function addModelRoute(Sitemap &$sitemap, string $base, string $field, Builder $query): void
    {
        // Get the most recent item
        $mostRecent = (clone $query)->max('updated_at');

        // Index page
        $sitemap->add(route("$base.index"), $mostRecent, '0.9', 'daily');

        // Item page
        foreach ($query->cursor() as $model) {
            $sitemap->add(
                route("$base.show", [$field => $model]),
                $model->updated_at,
                '0.8',
                'weekly'
            );
        }
    }
}
