<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Roumen\Sitemap\Sitemap;
use Corcel\Model\Post;
use App\Models\Page;
use App\Activity;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

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
        // Reject if the user can't handle XML
        if (!$request->accepts('text/xml')) {
            return new NotAcceptableHttpException(
                'Sitemap is only available as XML, but you don\'t seem to want that.'
            );
        }

        $map = app('sitemap');
        // $map->setCache('sitemap', 60);

        if (!$map->isCached() || config('app.debug', false)) {
            $this->buildSitemap($map);
        }

        return $map->render('xml');
    }

    private function buildSitemap(Sitemap &$sitemap)
    {
        // Get change dates for archive pages
        $postLast = Post::published()->newest()->first();
        $activityLast = Activity::published()->newest()->first();

        // Most important pages
        $sitemap->add(secure_url('/'), null, '1.0', 'weekly');
        $sitemap->add(secure_url('/activiteiten'), optional($postLast)->post_modified, '1.0', 'daily');
        $sitemap->add(secure_url('/nieuws'), optional($activityLast)->post_modified, '1.0', 'daily');

        // dd([
        //     'page' => Page::published()->get(),
        //     'post' => Post::published()->get(),
        //     'activity' => Activity::published()->get()
        // ]);

        // WordPress Pages
        foreach (Page::published()->get() as $page) {
            $sitemap->add(secure_url($page->slug), $page->post_modified, 0.7, 'weekly');
        }

        // WordPress Posts
        foreach (Post::published()->get() as $post) {
            $sitemap->add(secure_url($post->slug), $post->post_modified, 0.5, 'monthly');
        }

        // WordPress Activities
        foreach (Activity::published()->get() as $activity) {
            $sitemap->add(secure_url($activity->slug), $activity->post_modified, 0.6, 'weekly');
        }

        // WordPress documents
        // Not indexed, due to privacy reasons

        // Other pages
        // TODO
    }
}
