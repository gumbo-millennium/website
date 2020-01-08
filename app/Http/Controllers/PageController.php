<?php

namespace App\Http\Controllers;

use Advoor\NovaEditorJs\NovaEditorJs;
use App\Models\Activity;
use App\Models\Page;
use Illuminate\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use JsonException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageController extends Controller
{
    /**
     * Renders the homepage
     *
     * @return Response
     */
    public function homepage()
    {
        $nextEvent = Activity::query()
            ->available()
            ->where('start_date', '>', now())
            ->orderBy('start_date')
            ->first();

        return view('content.home', [
            'nextEvent' => $nextEvent
        ]);
    }

    /**
     * Handles fallback routes
     *
     * @return Response
     */
    public function fallback(Request $request)
    {
        return app()->call(
            \Closure::fromCallable([$this, 'render']),
            [
                trim($request->path(), '/\\')
            ]
        );
    }

    /**
     * Renders a single page, if possible
     *
     * @param string $slug
     * @return Response
     */
    protected function render(Repository $cache, string $slug)
    {
        $safeSlug = Str::slug(str_replace('/', '-slash-', Str::ascii($slug)));

        // Form cache key
        $cacheKey = sprintf('cache.%s', Str::slug($slug, '--'));

        // Check cache
        if ($cache->has($cacheKey)) {
            return view('content.page')->with([
                'page' => $cache->get($cacheKey)
            ]);
        }

        // Create instance
        $content = null;

        // Check filesystem
        $pagePath = sprintf('assets/json/pages/%s.json', Str::slug($slug));
        $fullPath = resource_path($pagePath);
        if (file_exists($fullPath)) {
            try {
                // Get file contents and decode json
                $content = json_decode(file_get_contents($fullPath));

                // Parse contents
                $contentText = \object_get($content, 'content', []);
                $content->html = NovaEditorJs::generateHtmlOutput($contentText);

                // Validate contents
                if (empty($content->html)) {
                    $content = null;
                }
            } catch (JsonException $exception) {
                logger()->error('Failed to parse json in {relative-path}: {exception}', [
                    ...compact('exception'),
                    'relative-path' => $pagePath,
                    'full-path' => $fullPath
                ]);
            }
        }

        // Check database if file failed
        if (!$content) {
            $content = Page::whereSlug($slug)->first() ?? Page::whereSlug(Page::SLUG_404)->first();

            if ($content || empty($content->html)) {
                $content = null;
            }
        }

        // 404 if still no results
        if (!$content) {
            abort(404);
        }

        // Store in cache
        $cache->put($cacheKey, $content, now()->addHours(6));

        // Show view
        return view('content.page')->with([
            'page' => $content
        ]);
    }
}
