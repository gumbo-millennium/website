<?php

use Illuminate\Support\Facades\Route;

/**
 * ADMIN ROUTES
 *
 * Here is where you can register admin web routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which contains
 * the "web", "auth" and "permission:admin" middleware groups.
 */

Route::redirect('/', '/admin/home');
Route::get('home', 'HomeController@index')->name('home');
// Route::get('members', 'MemberController@index')->name('members');
// Route::get('events', 'EventController@index')->name('events');

// WordPress login
Route::get('wordpress', 'WordPressController@index')->middleware(['permission:content'])->name('wordpress');
Route::post('wordpress', 'WordPressController@login')->middleware(['permission:content']);

/**
 * File / Document system. Handles files *and* categories
 */
Route::prefix('files')->name('files.')->middleware('can:manage,App\File')->group(function () {
    // Index page, optionally per category
    $this->get('/', 'FileCategoryController@index')
        ->name('index');

    // Category management
    $this->get('/category/add/', 'FileCategoryController@create')
        ->name('category.create');
    $this->post('/category/add/', 'FileCategoryController@store');

    $this->get('/category/edit/{category}', 'FileCategoryController@edit')
        ->name('category.edit');
    $this->patch('/category/edit/{category}', 'FileCategoryController@update');

    $this->get('/category/remove/{category}', 'FileCategoryController@remove')
        ->name('category.remove');
    $this->delete('/category/remove/{category}', 'FileCategoryController@destroy');

    // Category overview
    $this->get('/category/{category}', 'FileController@browse')
        ->name('browse');

    // Uploads
    $this->post('/upload/{category?}', 'FileController@upload')
        ->name('upload')
        ->middleware('can:create,App\File');

    // View and edit
    $this->get('/file/{file}', 'FileController@show')
        ->name('show');

    $this->put('/file/{file}', 'FileController@edit')
        ->name('edit')
        ->middleware('can:update,file');

    // Publish or un-publish
    $this->patch('/file/{file}/publish', 'FileController@publish')
        ->name('publish')
        ->middleware('can:publish,file');

    // Create PDF/A version
    $this->patch('/file/{file}/pdf-a', 'FileController@pdfa')
        ->name('pdfa')
        ->middleware('can:update,file');

    // Deletion request
    $this->delete('/file/{file}', 'FileController@delete')
        ->name('delete')
        ->middleware('can:delete,file');

    // Download request
    $this->get('/download/{file}', 'FileController@download')
        ->name('download')
        ->middleware('can:download,file');
});


/**
 * File / Document system. Handles files *and* categories
 */
Route::prefix('sponsors')->name('sponsors.')->middleware('permission:sponsor-edit')->group(function () {
    // Index page
    $this->get('/', 'SponsorController@index')
        ->name('index');

    // Insert sponsor
    $this->get('/add', 'SponsorController@create')->name('insert');
    $this->post('/add', 'SponsorController@insert');

    // Update sponsor
    $this->get('/{sponsor}', 'SponsorController@edit')->name('update');
    $this->put('/{sponsor}', 'SponsorController@store');

    // Delete sponsor
    $this->get('/{sponsor}/delete', 'SponsorController@delete')->name('delete');
    $this->delete('/{sponsor}/delete', 'SponsorController@destroy');
});
