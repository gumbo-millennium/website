<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\FileCategory;
use App\File;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handles file index, file viewing and file downloads
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class FileController extends Controller
{
    /**
     * Homepage
     *
     * @return Response
     */
    public function index()
    {
        $allCategories = FileCategory::has('files')->get();
        $defaultCategory = FileCategory::findDefault();

        $categoryList = collect();

        foreach ($allCategories as $category) {
            if ($defaultCategory && $defaultCategory->is($category)) {
                continue;
            }

            $categoryList->push($category);
        }

        $categoryList = $categoryList->sortBy('title');

        if ($defaultCategory) {
            $categoryList->push($defaultCategory);
        }

        // Get a base query
        $baseQuery = File::public()->available();
        $columns = ['id', 'slug', 'title', 'filename'];
        $limit = 5;

        return view('files.index')->with([
            'categories' => $categoryList,
            'files' => [
                'newest' => $baseQuery->latest()->take($limit)->get(),
                'popular' => [],
                'random' => $baseQuery->inRandomOrder()->take($limit)->get(),
            ]
        ]);
    }

    /**
     * Shows all the files in a given category, ordered by newest
     *
     * @param Request $request
     * @param FileCategory $category
     * @return Response
     */
    public function category(Request $request, FileCategory $category)
    {
        // Get most recent files
        $files = $category->files()->latest()->paginate(20);

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
        return view('files.single')->with([
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
        $filePath = $file->path;
        $fileName = $file->filename;

        // Report 404 if not public
        if ($file->public || $file->broken) {
            throw new NotFoundHttpException;
        }

        // Abort if file is missing
        if (!Storage::exists($filePath)) {
            throw new NotFoundHttpException;
        }

        return Storage::download($filePath, $fileName);
    }
}
