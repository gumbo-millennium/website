<?php

namespace App\Http\Controllers;

use Advoor\NovaEditorJs\NovaEditorJs;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\Page;
use Carbon\Carbon;
use Illuminate\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use JsonException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageController extends Controller
{
    private const PAGE_DIRECTORY = 'assets/json/pages';
    private const PAGE_REGEX = '/^([a-z0-9\-]+)\.json$/';
    private const PAGE_FILE_TEMPLATE = '%s/%s.json';
    /**
     * Renders the homepage
     *
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
        $content = $cache->get($cacheKey);
        if (!$cache->has($cacheKey)) {
            // Create instance
            $content = null;

            // Check filesystem
            $pagePath = sprintf(self::PAGE_FILE_TEMPLATE, self::PAGE_DIRECTORY, $safeSlug);
            $fullPath = resource_path($pagePath);
            if (file_exists($fullPath)) {
                try {
                    // Get file contents and decode json
                    $content = json_decode(file_get_contents($fullPath));

                    // Ensure dates
                    $nowDate = Carbon::createFromTimestamp(filemtime($fullPath))->toIso8601String();
                    $content->updated_at = $content->updated_at ?? $nowDate;
                    $content->created_at = $content->created_at ?? $content->updated_at;

                    // Parse dates
                    $content->created_at = Carbon::parse($content->created_at);
                    $content->updated_at = Carbon::parse($content->updated_at);

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
                $content = Page::whereSlug($safeSlug)->first() ?? Page::whereSlug(Page::SLUG_404)->first();

                if ($content || empty($content->html)) {
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

    /**
     * Returns list of pages versioned in the code
     * @return array
     */
    public function getVersionedPages(): array
    {
        // Get path
        $directory = resource_path(self::PAGE_DIRECTORY);

        // Skip if missing
        if (!\file_exists($directory) || !\is_dir($directory)) {
            return [];
        }

        // Get files
        $files = scandir($directory);

        // Return if scan failed
        if (!$files) {
            return [];
        }

        // Map
        $foundFiles = [];

        // Loop files
        foreach ($files as $file) {
            if (!\preg_match(self::PAGE_REGEX, $file, $matches)) {
                continue;
            }

            $foundFiles[$matches[1]] = json_decode(file_get_contents($directory . DIRECTORY_SEPARATOR . $file), true);
        }

        // Return
        return $foundFiles;
    }
}
