<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\FileCategory;

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

        return view('files.index')->with([
            'categories' => $categoryList
        ]);
    }

    public function category(Request $request, FileCategory $category)
    {
        $files = $category->files;

        // Render view
        return view('files.category')->with([
            'category' => $category,
            'files' => $files
        ]);
    }
}
