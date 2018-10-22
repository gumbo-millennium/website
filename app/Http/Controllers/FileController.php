<?php

namespace App\Http\Controllers;

use App\Option;
use App\Taxonomy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Handles file index, file viewing and file downloads
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class FileController extends Controller
{
    /**
     * Retuns a Taxonmy query builder
     *
     * @param Builder
     * @return Builder
     */
    protected function getTaxonomyQuery()
    {
        return Taxonomy::query()
            ->category()
            ->with('posts');
    }

    /**
     * Homepage
     *
     * @return Response
     */
    public function index()
    {
        $defaultCategory = Option::get('default_category');
        $allCategories = $this->getTaxonomyQuery()->get();

        $categoryList = collect();
        $defaultCategory = null;

        foreach ($allCategories as $category) {
            if ($category->ID === $defaultCategory) {
                $defaultCategory = $category;
            } else {
                $categoryList->push($category);
            }
        }

        $categoryList = $categoryList->sortBy('name');

        if ($defaultCategory) {
            $categoryList->push($defaultCategory);
        }

        return view('files.index')->with([
            'categories' => $categoryList
        ]);
    }

    public function category(Request $request, string $slug)
    {
        $baseQuery = $this->getTaxonomyQuery()->slug($slug);
        $category = $baseQuery->first();
        $posts = $category->posts()->get();

        // Render view
        return view('files.category')->with([
            'category' => $category,
            'posts' => $posts
        ]);
    }
}
