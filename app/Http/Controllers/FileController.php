<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\Str;
use App\Models\FileBundle;
use App\Models\FileCategory;
use App\Models\FileDownload;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LogicException;
use Spatie\MediaLibrary\MediaStream;
use Spatie\MediaLibrary\Models\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handles the user aspect of files.
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class FileController extends Controller
{
    /**
     * Makes sure the user is allowed to handle files.
     * @return void
     */
    public function __construct()
    {
        // Ensure users are logged in
        $this->middleware(['auth', 'permission:file-view']);
    }

    /**
     * Homepage
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

        $categoryQuery = $categoryQuery
            ->withCount('bundles');

        // Get items
        $categories = $categoryQuery
            ->withAvailable()
            ->withCount('bundles')
            ->orderByDesc('updated_at')
            ->orderBy('title')
            ->get();

        // Show view
        return response()
            ->view('files.index', compact('categories'))
            ->setPrivate();
    }

    /**
     * Shows all the files in a given category, ordered by newest
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

        // Render view
        return view('files.category')->with(compact('category', 'bundles'));
    }

    /**
     * Returns a single file's detail page
     * @param Request $request
     * @param FileBundle $bundle
     * @return Response
     */
    public function show(FileBundle $bundle)
    {
        if (!$bundle->is_available) {
            throw new NotFoundHttpException();
        }

        $bundle->loadMissing('media', 'category');
        $bundleMedia = $bundle->getMedia();
        return view('files.show')->with(compact('bundle', 'bundleMedia'));
    }

    /**
     * Logs a download
     * @param Request $request
     * @param null|FileBundle $bundle
     * @param null|Media $media
     * @return void
     * @throws LogicException
     * @throws ConflictingHeadersException
     */
    private function log(Request $request, ?FileBundle $bundle, ?Media $media): void
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
            'user_agent' => $request->userAgent()
        ]);
    }

    /**
     * Streams a zipfile to the user
     * @param Request $request
     * @param FileBundle $bundle
     * @return MediaStream
     */
    public function download(Request $request, FileBundle $bundle): Responsable
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
        return MediaStream::create("{$filename}.zip")->addMedia($media);
    }

    /**
     * Returns a single file download
     * @param Request $request
     * @param Media $media
     * @return BinaryFileResponse
     * @throws InvalidUrlGenerator
     * @throws InvalidConversion
     */
    public function downloadSingle(Request $request, Media $media): BinaryFileResponse
    {
        $bundle = $media->model();
        if (!$bundle instanceof FileBundle) {
            throw new NotFoundHttpException();
        }

        // Check permissions
        $this->authorize('download', $bundle);

        // Log bundle download
        $this->log($request, $bundle, $media);

        // Send single file
        return response()->download($media->getPath(), $media->file_name);
    }
}
