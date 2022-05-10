<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Page;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Cache\Repository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

class PageController extends Controller
{
    private Repository $cache;

    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
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

        return Response::view('content.group', [
            'pages' => $pages,
            'page' => $page,
            'group' => $group,
        ])->setLastModified($lastModified);
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
        SEOTools::addImages([
            image_asset($page->cover)->preset('social'),
        ]);

        // Show view
        return Response::view('content.page', [
            'page' => $page,
        ])->setLastModified($page->updated_at);
    }
}
