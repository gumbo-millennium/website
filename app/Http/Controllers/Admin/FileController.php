<?php

namespace App\Http\Controllers\Admin;

use App\File;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\FileCategory;
use App\Http\Requests\NewFileRequest;
use App\Jobs\FileProcessingJob;

class FileController extends Controller
{
    public function __construct()
    {
        $this->var = $var;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $files = File::orderBy('created_at', 'DESC')->paginate(20);

        return view('admin.files.index')->with(['files' => $files]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(NewFileRequest $request)
    {
        // We need to get a file
        if (!$request->hasFile('file')) {
            abort(400, 'File is missing from request');
        }

        $upload = $request->file('file');
        $stored = $upload->store(File::STORAGE_DIR);
        $filename = $upload->getClientOriginalName();

        // Build a file based on this upload
        dd([
            'path' => $stored,
            'public' => false,
            'title' => $filename,
            'filename' => $filename,
            'filesize' => filesize(storage_path($stored)),
            'owner' => $request->user()
        ]);

        // Register and save file
        $file = new File($config);
        $file->save();

        $defaultCategory = FileCategory::findDefault();
        if ($defaultCategory) {
            $file->categories()->assign(FileCategory::findDefault());
        }

        // Trigger processing job
        dispatch(new FileProcessingJob($file));
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
