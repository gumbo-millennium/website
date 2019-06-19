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
    Route::get('/', 'FileCategoryController@index')
        ->name('index');

    // Category management
    Route::get('/category/add/', 'FileCategoryController@create')
        ->name('category.create');
    Route::post('/category/add/', 'FileCategoryController@store');

    Route::get('/category/edit/{category}', 'FileCategoryController@edit')
        ->name('category.edit');
    Route::patch('/category/edit/{category}', 'FileCategoryController@update');

    Route::get('/category/remove/{category}', 'FileCategoryController@remove')
        ->name('category.remove');
    Route::delete('/category/remove/{category}', 'FileCategoryController@destroy');

    // Category overview
    Route::get('/category/{category}', 'FileController@browse')
        ->name('browse');

    // Uploads
    Route::post('/upload/{category?}', 'FileController@upload')
        ->name('upload')
        ->middleware('can:create,App\File');

    // View and edit
    Route::get('/file/{file}', 'FileController@show')
        ->name('show');

    Route::put('/file/{file}', 'FileController@edit')
        ->name('edit')
        ->middleware('can:update,file');

    // Publish or un-publish
    Route::patch('/file/{file}/publish', 'FileController@publish')
        ->name('publish')
        ->middleware('can:publish,file');

    // Create PDF/A version
    Route::patch('/file/{file}/pdf-a', 'FileController@pdfa')
        ->name('pdfa')
        ->middleware('can:update,file');

    // Deletion request
    Route::delete('/file/{file}', 'FileController@delete')
        ->name('delete')
        ->middleware('can:delete,file');

    // Download request
    Route::get('/download/{file}', 'FileController@download')
        ->name('download')
        ->middleware('can:download,file');
});


/**
 * File / Document system. Handles files *and* categories
 */
Route::prefix('sponsors')->name('sponsors.')->middleware('permission:sponsor-edit')->group(function () {
    // Index page
    Route::get('/', 'SponsorController@index')
        ->name('index');

    // Insert sponsor
    Route::get('/add', 'SponsorController@create')->name('insert');
    Route::post('/add', 'SponsorController@insert');

    // Update sponsor
    Route::get('/{sponsor}', 'SponsorController@edit')->name('update');
    Route::put('/{sponsor}', 'SponsorController@store');

    // Delete sponsor
    Route::get('/{sponsor}/delete', 'SponsorController@delete')->name('delete');
    Route::delete('/{sponsor}/delete', 'SponsorController@destroy');
});
