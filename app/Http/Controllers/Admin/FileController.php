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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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

        // Move file to uploads
        $upload = $request->file('file');
        $stored = Storage::putFile(File::STORAGE_DIR, $upload);
        $filename = $upload->getClientOriginalName();

        // Build a file based on this upload
        $config = [
            'path' => $stored,
            'public' => false,
            'title' => $filename,
            'filename' => $filename,
            'filesize' => Storage::size($stored)
        ];

        // Get user ID
        $user = $request->user();
        $userId = $user->getAuthIdentifier();

        // Register and save file
        $file = new File($config);
        $file->owner = $userId;
        $file->save();

        // Assign the category to the file
        $file->categories()->attach($category);

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
        // Toggle if public
        if ($request->has('public')) {
            $file->public = (bool) $request->validated()->public;
        }

        // Change title, and the slug if private
        if ($request->has('title')) {
            $file->title = (string) $request->validated()->title;
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

        // Redirect back
        return redirect()->route('admin.files.list', [
            'category' =>$category ?? $file->categories->first()
        ])->with([
            'status' => sprintf('Het bestand %s is bijgewerkt', $file->display_title)
        ]);
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
        $request->validate([
            'public' => 'required|boolean'
        ]);

        // Get validated data
        $shouldPublic = $request->validated()->public;

        // Update public value
        $file->public = $shouldPublic;
        $file->save();

        // Get correct message
        if ($file->public) {
            $message = 'Het bestand %s is %s gepubliceerd.';
        } else {
            $message = 'Het bestand % is %s verborgen voor bezoekers.';
        }

        // Flash update message
        return back()->with(['status' => sprintf(
            $message,
            $file->display_title,
            $file->public === $shouldPublic ? 'succesvol' : 'NIET'
        )]);
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
