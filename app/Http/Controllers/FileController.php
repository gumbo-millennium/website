<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\Str;
use App\Models\FileBundle;
use App\Models\FileCategory;
use App\Models\FileDownload;
use App\Models\Media;
use Artesaos\SEOTools\Facades\SEOTools;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use LogicException;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
use Spatie\MediaLibrary\Support\MediaStream;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handles the user aspect of files.
 */
class FileController extends Controller
{
    /**
     * Makes sure the user is allowed to handle files.
     *
     * @return void
     */
    public function __construct()
    {
        // Ensure users are logged in
        $this->middleware(['auth', 'permission:file-view']);

        // Ensure all responses are private
        $this->middleware(static function ($request, Closure $next) {
            // Forward
            $response = $next($request);

            // Add cache headers if possible
            if ($response instanceof HttpResponse) {
                $response->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
            }

            // Return response
            return $response;
        });
    }

    /**
     * Homepage.
     */
    public function index(): HttpResponse
    {
        // Try to only get non-empty categories
        $categoryQuery = FileCategory::whereAvailable();

        // Ignore if that's impossible
        if (! (clone $categoryQuery)->exists()) {
            $categoryQuery = FileCategory::query();
        }

        // Get categories with their associated bundles
        $categoryQuery = $categoryQuery
            ->withCount('bundles');

        // Get items
        $categories = $categoryQuery
            ->withAvailable()
            ->withCount('bundles')
            ->orderByDesc('updated_at')
            ->orderBy('title')
            ->get();

        // Set title
        SEOTools::setTitle('Bestanden');
        SEOTools::setCanonical(route('files.index'));

        // Show view
        return Response::view('files.index', compact('categories'));
    }

    /**
     * Shows all the files in a given category, ordered by newest.
     */
    public function category(FileCategory $category): HttpResponse
    {
        // Get most recent files
        $bundles = $category
            ->bundles()
            ->whereAvailable()
            ->paginate(20);

        // Set title
        SEOTools::setTitle("{$category->title} - Bestanden");
        SEOTools::setCanonical(route('files.category', compact('category')));

        // Render view
        return Response::view('files.category', compact('category', 'bundles'));
    }

    /**
     * Returns a single file's detail page.
     */
    public function show(FileBundle $bundle): HttpResponse
    {
        if (! $bundle->is_available) {
            throw new NotFoundHttpException();
        }

        // Load extras
        $bundle->loadMissing('media', 'category');
        $bundleMedia = $bundle
            ->getMedia()
            ->sortBy('name', SORT_REGULAR, $bundle->sort_order === 'desc');

        // Set title
        SEOTools::setTitle("{$bundle->title} - {$bundle->category->title} - Bestanden");
        SEOTools::setCanonical(route('files.show', compact('bundle')));

        // Render view
        return Response::view('files.show', compact('bundle', 'bundleMedia'));
    }

    /**
     * Streams a zipfile to the user.
     */
    public function download(Request $request, FileBundle $bundle): StreamedResponse
    {
        // Check permissions
        $this->authorize('download', $bundle);

        // Softfail if not published
        abort_unless($bundle->is_available, 404);

        // Log bundle download
        $this->log($request, $bundle, null);

        // Determine a proper filename
        $filename = Str::ascii($bundle->title, 'nl');

        // Get all media
        $media = $bundle->getMedia();

        // Stream a zip to the user
        return MediaStream::create("{$filename}.zip")
            ->addMedia($media)
            ->toResponse($request);
    }

    /**
     * Returns a single file download.
     *
     * @param Media $media
     * @throws InvalidUrlGenerator
     * @throws InvalidConversion
     */
    public function downloadSingle(Request $request, SpatieMedia $media): SymfonyResponse
    {
        $bundle = $media->model;
        abort_unless($bundle instanceof FileBundle, HttpResponse::HTTP_NOT_FOUND, 'Invalid media attachment');

        // Check permissions
        $this->authorize('download', $bundle);

        // Softfail if not published
        abort_unless($bundle->is_available, HttpResponse::HTTP_NOT_FOUND, 'Bundle not available');

        // Log bundle download
        $this->log($request, $bundle, $media);

        // Find disk
        $storageDisk = Storage::disk($media->disk);

        // Find file in disk
        $storageBasePath = $storageDisk->path('');
        $mediaPath = Str::after($media->getPath(), $storageBasePath);

        // Skip if not found
        abort_unless($storageDisk->exists($mediaPath), HttpResponse::HTTP_NOT_FOUND, 'Disk error');

        // Check if the file is external (cloud-hosted)
        try {
            $temporaryUrl = $storageDisk->temporaryUrl($mediaPath, Date::now()->addMinutes(5));
            if ($temporaryUrl) {
                // Redirect but ensure browser won't replay
                return Response::redirectTo($temporaryUrl, HttpResponse::HTTP_FOUND, [
                    'Cache-Control' => 'no-store',
                ]);
            }

            // No URL, stream it
        } catch (RuntimeException $exception) {
            // No worries, stream it
        }

        return Response::stream(fn () => $storageDisk->readStream($mediaPath), 200, [
            'Content-Type' => $media->mime_type,
            'Content-Disposition' => sprintf('attachment; filename="%s"', Str::ascii($media->file_name)),
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    /**
     * Finds files.
     *
     * @return Response
     */
    public function search(Request $request)
    {
        // Require a search query
        $searchQuery = $request->get('query');
        if (empty($searchQuery)) {
            return Response::redirectToRoute('files.index');
        }

        // Set title
        SEOTools::setTitle("{$searchQuery} - Zoeken - Bestanden");
        SEOTools::setCanonical(route('files.search', ['query' => $searchQuery]));

        // Only return files in available bundles
        $constraint = Media::query()
            ->with('model')
            ->whereModelType(FileBundle::class)
            ->whereIn('model_id', Cache::remember(
                'files.search.file-ids',
                Date::now()->addHour(),
                static fn () => FileBundle::whereAvailable()->pluck('id'),
            ));

        // Perform the search query
        $files = Media::search($searchQuery)->constrain($constraint);

        // Order by date and paginate results
        $results = $files->paginate(30);

        // Return result
        return Response::view('files.search', [
            'files' => $results,
            'searchQuery' => $searchQuery,
        ]);
    }

    /**
     * Logs a download.
     *
     * @param null|Media $media
     * @throws LogicException
     * @throws ConflictingHeadersException
     */
    private function log(Request $request, ?FileBundle $bundle, ?SpatieMedia $media): void
    {
        // Fail
        if (empty($bundle) && empty($media)) {
            throw new LogicException('Cannot log when neither bundle nor media is present');
        }

        // Log download
        FileDownload::create([
            'user_id' => $request->user()->id,
            'bundle_id' => $bundle ? $bundle->id : $media->model->id,
            'media_id' => optional($media)->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
