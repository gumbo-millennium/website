<?php

namespace App\Http\Controllers\Admin;

use App\Models\File;
use App\Models\FileCategory;
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
use App\Jobs\FileArchiveJob;

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
        $nextRoute = ($nextCategory === null) ? 'admin.files.index' : 'admin.files.browse';

        // Forward
        return redirect()
            ->route($nextRoute, ['category' => $nextCategory]);
    }

    /**
     * Lists files in the category
     *
     * @param FileCategory $category
     * @return void
     */
    public function browse(FileCategory $category)
    {
        $files = $category->
            files()->
            with('owner')
            ->paginate(20);

        return view('admin.files.browse')->with([
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
            __('files.messages.update', ['file' => $file->display_title])
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
        $messageName = $file->public ? 'files.messages.publish' : 'files.messages.unpublish';

        // Redirect
        return $this->continue(
            $category ?? $file->categories->first(),
            __($messageName, ['file' => $file->display_title])
        );
    }

    /**
     * Schedules the file to be converted to PDF/A
     *
     * @param Request $request
     * @param File $file
     * @param FileCategory $category
     * @return Response
     */
    public function pdfa(File $file, FileCategory $category = null)
    {
        if (!$file->hasState(File::STATE_PDFA)) {
            dispatch(new FileArchiveJob($file));
            $message = 'files.messages.pdfa-started';
        } else {
            $message = 'files.messages.pdfa-already';
        }

        // Redirect
        return $this->continue(
            $category ?? $file->categories->first(),
            __($message, ['file' => $file->display_title])
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

        return $this->continue(
            $nextCategory,
            __('files.messages.destroyed', ['file' => $file->display_title])
        );
    }

    /**
     * Provides a download, if the file is available on the storage and not broken.
     *
     * @param Request $request
     * @param File $file
     * @return Response
     */
    public function download(Request $request, File $file)
    {
        $filePath = $file->path;
        $fileName = $file->filename;

        // Report 404 if not public
        if ($file->broken || !Storage::exists($filePath)) {
            throw new NotFoundHttpException();
        }

        return Storage::download($filePath, $fileName);
    }

    /**
     * Starts a job to convert the file to a PDF/A file.
     *
     * @param Request $request
     * @param File $file
     * @return Response
     */
    public function archive(File $file, FileCategory $category)
    {
        if ($file->hasState(File::STATE_PDFA)) {
            return redirect()->back()->with([
                'status',
                __('files.messages.pdfa-already', ['file' => $file->display_title])
            ]);
        }

        // Start a job
        FileArchiveJob::dispatch($file);

        // Store new status
        session()->flash(
            'status',
            __('files.messages.pdfa-started', ['file' => $file->display_title])
        );

        // Redirect back
        return redirect()->back()->with([
            'status',
            __('files.messages.pdfa-started', ['file' => $file->display_title])
        ]);
    }
}
