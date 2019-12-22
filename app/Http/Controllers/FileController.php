<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\FileCategory;
use App\Models\File;
use App\Models\FileDownload;
use Czim\Paperclip\Contracts\AttachmentInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
     * Files shown as summary
     */
    private const TOP_FILE_LIMIT = 5;
    /**
     * Makes sure the user is allowed to handle files.
     *
     * @return void
     */
    public function __construct()
    {
        // Ensure users are logged in
        $this->middleware(['auth', 'permission:file-view']);
    }

    /**
     * Homepage
     *
     * @return Response
     */
    public function index()
    {
        // Try to only get non-empty categories
        $categoryQuery = FileCategory::has('files');

        // Ignore if that's impossible
        if (!(clone $categoryQuery)->exists()) {
            $categoryQuery = FileCategory::query();
        }

        // Get items
        $categoryList = $categoryQuery->withCount('files')->orderBy('title')->get();

        // Get a base query
        $baseQuery = File::query();

        // Get queries for new, popular and random files
        $baseQuery = File::query()->take(self::TOP_FILE_LIMIT);
        $queries = collect([
            'newest' => $baseQuery->latest(),
            'popular' => $baseQuery->withCount('downloads')->orderBy('downloads_count', 'DESC')->latest(),
            'random' => $baseQuery->inRandomOrder(),
        ])->each->get();

        // Show view
        return view('files.index')->with([
            'categories' => $categoryList,
            'files' => $queries
        ]);
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
        $files = $category->files()->paginate(20);

        // Render view
        return view('files.category')->with([
            'category' => $category,
            'files' => $files
        ]);
    }

    /**
     * Returns a single file's detail page
     *
     * @param Request $request
     * @param File $file
     * @return Response
     */
    public function show(Request $request, File $file)
    {
        return view('files.show')->with([
            'file' => $file,
            'user' => $request->user()
        ]);
    }

    /**
     * Provides a download, if the file is public, available on the storage and not broken.
     *
     * @param Request $request
     * @param File $file
     * @return Response
     */
    public function download(Request $request, File $file)
    {
        /** @var AttachmentInterface $attachment */
        $attachment = $file->file;

        // Abort if file is missing
        if (!$attachment || !$attachment->exists()) {
            throw new NotFoundHttpException();
        }

        // Log download
        FileDownload::create([
            'user_id' => $request->user()->id,
            'file_id' => $file->id,
            'ip' => $request->ip()
        ]);


        // Get some calculation properties
        $disk = $attachment->getStorage();
        $path = $attachment->path();

        // Get download
        return Storage::disk($disk)->download($path, $attachment->originalFilename());
    }
}
