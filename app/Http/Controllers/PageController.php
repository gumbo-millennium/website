<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\Str;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\Page;
use Illuminate\Cache\Repository;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Renders the homepage
     * @return Response
     */
    public function homepage(Request $request)
    {
        $nextEvents = Activity::query()
            ->available()
            ->where('start_date', '>', now())
            ->orderBy('start_date')
            ->take(2)
            ->get();

        $enrollments = [];
        if ($request->user() && $nextEvents) {
            $enrollments = Enrollment::query()
                ->whereUserId($request->user()->id)
                ->where('activity_id', 'in', $nextEvents->pluck('id'))
                ->orderBy('created_at', 'asc')
                ->get()
                ->keyBy('activity_id');
        }

        // Return view
        return response()
            ->view('content.home', compact('nextEvents', 'enrollments'));
    }

    /**
     * Handles fallback routes
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
     * @param string $slug
     * @return Response
     */
    protected function render(Repository $cache, string $slug)
    {
        $safeSlug = Str::slug(str_replace('/', '-slash-', Str::ascii($slug)));

        // Form cache key
        $cacheKey = sprintf('cache.%s', Str::slug($slug, '--'));

        // Check cache
        $content = $cache->get($cacheKey);
        if (!$cache->has($cacheKey)) {
            // Create instance
            $content = null;

            // Check database
            if (!$content) {
                $content = Page::whereSlug($safeSlug)->first() ?? Page::whereSlug(Page::SLUG_404)->first();

                if (!$content || empty($content->html)) {
                    $content = null;
                }
            }

            // 404 if still no results
            if (!$content) {
                abort(404);
            }

            // Store in cache
            $cache->put($cacheKey, $content, now()->addHour());
        }

        // Show view
        return response()
            ->view('content.page', ['page' => $content])
            ->withHeaders([
                'Last-Modified' => $content->updated_at->toRfc7231String(),
                'Expires' => now()->addHour()->toRfc7231String(),
                'Cache-Control' => ['public', 'must-revalidate', 'max-age=3600']
            ]);
    }
}
