<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\Str;
use App\Models\FileBundle;
use App\Models\FileCategory;
use App\Models\FileDownload;
use App\Models\Media;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use LogicException;
use Spatie\MediaLibrary\MediaStream;
use Spatie\MediaLibrary\Models\Media as SpatieMedia;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handles the user aspect of files.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
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
        $this->middleware(static function ($request, \Closure $next) {
            // Forward
            $response = $next($request);

            // Add cache headers if possible
            if ($response instanceof Response) {
                $response->setCache([
                    'max_age' => 0,
                    'private' => true,
                ]);
            }

            // Return response
            return $response;
        });
    }

    /**
     * Homepage
     *
     * @return Response
     */
    public function index(): Response
    {
        // Try to only get non-empty categories
        $categoryQuery = FileCategory::whereAvailable();

        // Ignore if that's impossible
        if (!(clone $categoryQuery)->exists()) {
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
        return response()
            ->view('files.index', compact('categories'))
            ->setPrivate();
    }

    /**
     * Shows all the files in a given category, ordered by newest
     *
     * @param FileCategory $category
     * @return Response
     */
    public function category(FileCategory $category)
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
        return response()
            ->view('files.category', compact('category', 'bundles'))
            ->setPrivate();
    }

    /**
     * Returns a single file's detail page
     *
     * @param Request $request
     * @param FileBundle $bundle
     * @return Response
     */
    public function show(FileBundle $bundle)
    {
        if (!$bundle->is_available) {
            throw new NotFoundHttpException();
        }

        // Load extras
        $bundle->loadMissing('media', 'category');
        $bundleMedia = $bundle
            ->getMedia()
            ->sortByDesc('name');

        // Set title
        SEOTools::setTitle("{$bundle->title} - {$bundle->category->title} - Bestanden");
        SEOTools::setCanonical(route('files.show', compact('bundle')));

        // Render view
        return response()
            ->view('files.show', compact('bundle', 'bundleMedia'))
            ->setPrivate();
    }

    /**
     * Streams a zipfile to the user
     *
     * @param Request $request
     * @param FileBundle $bundle
     * @return SymfonyResponse
     */
    public function download(Request $request, FileBundle $bundle): SymfonyResponse
    {
        // Check permissions
        $this->authorize('download', $bundle);

        // Log bundle download
        $this->log($request, $bundle, null);

        // Determine a proper filename
        $filename = Str::ascii($bundle->title, 'nl');

        // Get all media
        $media = $bundle->getMedia();

        // Stream a zip to the user
        return MediaStream::create("{$filename}.zip")
            ->addMedia($media)
            ->toResponse($request)
            ->setPrivate();
    }

    /**
     * Returns a single file download
     *
     * @param Request $request
     * @param Media $media
     * @return BinaryFileResponse
     * @throws InvalidUrlGenerator
     * @throws InvalidConversion
     */
    public function downloadSingle(Request $request, SpatieMedia $media): BinaryFileResponse
    {
        $bundle = $media->model;
        if (!$bundle instanceof FileBundle) {
            throw new NotFoundHttpException();
        }

        // Check permissions
        $this->authorize('download', $bundle);

        // Log bundle download
        $this->log($request, $bundle, $media);

        // Send single file
        return response()
            ->download($media->getPath(), $media->file_name)
            ->setPrivate();
    }

    /**
     * Finds files
     *
     * @param Request $request
     * @param string $searchQuery
     * @return Response
     */
    public function search(Request $request)
    {
        // Require a search query
        $searchQuery = $request->get('query');
        if (empty($searchQuery)) {
            return \response()
                ->redirectToRoute('files.index');
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
                static fn () => FileBundle::whereAvailable()->pluck('id')
            ));

        // Perform the search query
        $files = Media::search($searchQuery)->constrain($constraint);

        // Order by date and paginate results
        $results = $files->paginate(30);

        // Return result
        return \response()
            ->view('files.search', [
                'files' => $results,
                'searchQuery' => $searchQuery,
            ]);
    }

    /**
     * Logs a download
     *
     * @param Request $request
     * @param FileBundle|null $bundle
     * @param Media|null $media
     * @return void
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
