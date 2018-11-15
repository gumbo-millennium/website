<?php

namespace App\Http\Controllers\Admin;

use App\File;
use App\FileCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FileCategoryController extends Controller
{
    /**
     * Removes the default category, with the given exception keeping it's default state.
     *
     * @param FileCategory $except
     * @return void
     */
    protected function removeDefault(FileCategory $except) : void
    {
        Category::where('default', '1')
            ->where('id', '!=', $except->id)
            ->update(['default' => 0]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = FileCategory::query()
            ->orderBy('default', 'ASC')
            ->orderBy('title', 'ASC')
            ->get();

        return view('admin.files.index')->with([
            'categories' => $categories,
            'totalFiles' => File::count(),
            'defaultCategory' => FileCategory::findDefault()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.files.category-create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate data
        $valid = $request->validate([
            'title' => 'required|string|min:2',
            'default' => 'optional|boolean'
        ]);

        // Attempt creation
        $category = FileCategory::create([
            'title' => $valid['title'],
            'default' => $valid['default'] ?? false
        ]);

        // Redirect back if failed
        if (!$category->exists) {
            return redirect()->back()->with([
                'status' => 'Categorie aanmaken mislukt'
            ]);
        }

        // Remove 'default' flag from other categories
        if ($category->default) {
            $this->removeDefault($category);
        }

        // Redirect to category file list
        return redirect()->route('admin.files.browse', [
            'category' => $category
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\FileCategory  $fileCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(FileCategory $fileCategory)
    {
        return view('admin.files.category-edit', [
            'category' => $fileCategory
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\FileCategory  $fileCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FileCategory $fileCategory)
    {
        // Validate data
        $valid = $request->validate([
            'title' => 'required|string|min:2',
            'default' => 'optional|boolean'
        ]);

        if ($valid['title'] !== $fileCategory->title) {
            $fileCategory->slug = null;
        }

        // Attempt update
        $fileCategory->fill([
            'title' => $valid['title'],
            'default' => $fileCategory->default ? : ($valid['default'] ?? false)
        ]);
        $ok = $fileCategory->save(['name', 'default', 'slug']);

        // Redirect back if failed
        if (!$ok) {
            return redirect()->back()->with([
                'status' => 'Categorie bijwerken mislukt'
            ]);
        }

        // Remove 'default' flag from other categories
        if ($categoryData->default) {
            $this->removeDefault($category);
        }

        // Redirect to category file list
        return redirect()->route('admin.files.browse', [
            'category' => $category
        ]);
    }

    /**
     * Show the form for deleting the specified resource.
     *
     * @param  \App\FileCategory  $fileCategory
     * @return \Illuminate\Http\Response
     */
    public function remove(FileCategory $fileCategory)
    {
        return view('admin.files.category-delete', [
            'category' => $fileCategory,
            'fileCount' => $fileCategory->files()->count()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\FileCategory  $fileCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(FileCategory $fileCategory)
    {
        // If the category is the default, remove the flag
        if ($fileCategory->default) {
            $fileCategory->default = false;
            $fileCategory->save();
        }

        // Find default category
        $defaultCategory = FileCategory::findDefault();

        // Count and get files
        $fileCount = $fileCategory->files()->count();
        $files = $fileCategory->files;

        // Unlink files
        $defaultCategory->files()->syncWithoutDetaching($files);
        $fileCategory->files()->detach($files);

        // Get ID
        $id = $fileCategory->id;

        // Remove category
        $fileCategory->delete();

        // Get category
        $category = FileCategory::find($id);

        if ($category === null) {
            $status = implode(' ', [
                trans('files.messages.category-removed', ['category' => $fileCategory->name]),
                trans_choice('files.messages.category-removed-files', $fileCount)
            ]);
        } else {
            $status = 'Something went fucky. Category has not been deleted';
        }

        // Report back
        return redirect()->route('admin.files.index')->with([
            'status' => $status
        ]);
    }
}
