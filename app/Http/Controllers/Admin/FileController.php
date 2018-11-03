<?php

namespace App\Http\Controllers\Admin;

use App\File;
use App\FileCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\NewFileRequest;
use App\Jobs\FileProcessingJob;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Testing\HttpException;
use App\Http\Requests\FileRequest;

/**
 * Handles uploads, changes and deletes for files uploaded
 * by the board of Gumbo Millennium
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class FileController extends Controller
{
    /**
     * Redirects the user to the page they came from, and flashes the given message
     * to the status
     *
     * @param FileCategory $category
     * @param string $message
     * @return Response
     */
    protected function continue(FileCategory $category, string $message)
    {
        // Flash data
        session()->flash('status', $message);

        // Determine route
        $nextCategory = $category ?? FileCategory::findDefault();
        $nextRoute = ($nextCategory === null) ? 'admin.files.index' : 'admin.files.list';

        // Forward
        return redirect()
            ->route($nextRoute, ['category' => $nextCategory]);
    }
    /**
     * Display a listing of the files available. Optionally inside a category
     *
     * @return \Illuminate\Http\Response
     */
    public function index(FileCategory $category = null)
    {
        $categories = FileCategory::query()
            ->orderBy('default', 'ASC')
            ->orderBy('title', 'ASC')
            ->paginate(20);

        return view('admin.files.index')->with([
            'categories' => $categories,
            'defaultCategory' => FileCategory::findDefault()
        ]);
    }

    /**
     * Lists files in the category
     *
     * @param FileCategory $category
     * @return void
     */
    public function list(FileCategory $category)
    {
        $files = $category->
            files()->
            with('owner')
            ->paginate(20);

        return view('admin.files.list')->with([
            'files' => $files,
            'category' => $category
        ]);
    }

    /**
     * Stores a new file in the database, in the provided category or
     * the default one
     *
     * @param NewFileRequest $request
     * @param FileCategory $category
     * @return Response
     */
    public function upload(NewFileRequest $request, FileCategory $category = null)
    {
        // We need to get a file
        if (!$request->hasFile('file')) {
            abort(400, 'File is missing from request');
        }

        // Get target category
        if ($category === null) {
            $category = FileCategory::findDefaultOrFail();
        }

        // Get requested file
        $upload = $request->file('file');

        // Get metadata from file
        $uploadMime = $upload->getMimeType();
        $uploadExtension = str_start($upload->extension(), '.');
        $uploadFilename = $upload->getClientOriginalName();
        $uploadFilesize = filesize($upload->path());
        $uploadName = str_before($uploadFilename, $uploadExtension);

        // Store file
        $stored = Storage::putFile(File::STORAGE_DIR, $upload);

        // Get the category
        $category = $category ?? FileCategory::findDefault();

        // Build a file based on this upload
        $config = [
            'path' => $stored,
            'public' => false,
            'title' => $uploadName,
            'filename' => $uploadFilename,
            'filesize' => $uploadFilesize,
            'mime' => $uploadMime
        ];

        // Create new file in the category
        $file = $category->files()->create($config);
        $file->owner()->associate($request->user());
        $file->save();

        // Return file info
        return response()->json([
            'ok' => true,
            'file' => $file
        ], Response::HTTP_CREATED);
    }

    /**
     * Shows information about the given file
     *
     * @param File $file
     * @param FileCategory $category
     * @return Response
     */
    public function show(File $file, FileCategory $category = null)
    {
        return view('admin.files.show', [
            'category' => $category ?? $file->categories->first(),
            'file' => $file
        ]);
    }

    /**
     * Show the form for editing the file.
     *
     * @param File $file
     * @param FileCategory $category
     * @return Response
     */
    public function edit(File $file, FileCategory $category = null)
    {
        return view('admin.files.edit', [
            'category' => $category ?? $file->categories->first(),
            'file' => $file
        ]);
    }

    /**
     * Updates the given file.The slug might change if the file is
     * NOT yet published.
     *
     * @param FileRequest $request
     * @param File $file
     * @param FileCategory $category
     * @return Response
     */
    public function update(FileRequest $request, File $file, FileCategory $category = null)
    {
        // Get valid data
        $validatedData = $request->validated();

        // Toggle if public
        if (isset($validatedData['public'])) {
            $file->public = (bool) $validatedData['public'];
        }

        // Change title, and the slug if private
        if (isset($validatedData['title'])) {
            $file->title = (string) $validatedData['title'];
            if (!$file->public) {
                $file->slug = null;
            }
        }

        // Store changes
        $file->save();

        // Ensure default category exists
        if (empty($file->categories)) {
            $file->categories()->assign(FileCategory::findDefault());
            $file->refresh();
        }

        // Redirect
        return $this->continue(
            $category ?? $file->categories->first(),
            sprintf('Het bestand %s is bijgewerkt', $file->display_title)
        );
    }

    /**
     * Flags the public bit on the given resource
     *
     * @param Request $request
     * @param File $file
     * @param FileCategory $category
     * @return Response
     */
    public function publish(Request $request, File $file, FileCategory $category = null)
    {
        // Make sure public is passed
        $validatedData = $request->validate([
            'public' => 'required|boolean'
        ]);

        // Get validated data
        $shouldPublic = $validatedData['public'];

        // Update public value
        $file->public = $shouldPublic;
        $file->save();

        // Get correct message
        if ($file->public) {
            $message = 'Het bestand <strong>%s</strong> is %s gepubliceerd.';
        } else {
            $message = 'Het bestand <strong>%s</strong> is %s verborgen voor bezoekers.';
        }

        // Make message
        $message = sprintf(
            $message,
            $file->display_title,
            $file->public === $shouldPublic ? 'succesvol' : 'NIET'
        );

        // Redirect
        return $this->continue(
            $category ?? $file->categories->first(),
            $message
        );
    }

    /**
     * Deletes the File, both from the DB as from the disk.
     *
     * @param File $file
     * @param FileCategory $category
     * @return Response
     */
    public function destroy(File $file, FileCategory $category = null)
    {
        // Figure out next category
        $nextCategory = $category ?? $file->categories->first();
        $file->delete();

        $nextRoute = ($nextCategory === null) ? 'admin.files.index' : 'files.admin.list';

        return redirect()
            ->with(['status' => "Het bestand {$file->display_title} is verwijderd"])
            ->route($nextRoute, ['category' => $nextCategory]);
    }
}
