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
     * Display the specified resource.
     *
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function show(File $file)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function edit(File $file)
    {
        $categories = FileCategory::all();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, File $file)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function destroy(File $file)
    {
        //
    }
}
