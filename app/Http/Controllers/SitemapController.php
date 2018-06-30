<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Roumen\Sitemap\Sitemap;

/**
 * Generates sitemaps for WordPress pages and our pages.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class SitemapController extends Controller
{
    /**
     * Make sure the request has a valid type before sending it
     *
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
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        // $map = app('sitemap');
        $map = new Sitemap;

        $map->setCache('sitemap', 60);

        if (!$map->isCached()) {
            $this->buildSitemap($map);
        }

        return $map->render('xml');
    }

    private function buildSitemap(Sitemap &$sitemap)
    {
        // Most important pages
        $sitemap->add(secure_url('/'), $oldest, '1.0', 'weekly');
        $sitemap->add(secure_url('/activiteiten'), null, '1.0', 'daily');
        $sitemap->add(secure_url('/nieuws'), null, '1.0', 'daily');

        // WordPressPages
        // TODO

        // WordPress posts
        // TODO

        // WordPress activities
        // TODO

        // Other pages
        // TODO
    }
}
