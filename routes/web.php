<?php

use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Home and privacy policy
Route::get('/', 'WordPressController@homepage')->name('home');
Route::get('/privacy-policy', 'WordPressController@privacy')->name('privacy');

// Sitemap
Route::get('/sitemap.xml', 'SitemapController@index')->name('sitemap');

// News route
Route::get('/news', 'NewsController@index');
Route::get('/news/{slug}', 'NewsController@post');

// Files route
Route::get('/files', 'FileController@index')
    ->name('files.index')
    ->middleware('can:browse,App\FileCategory');
Route::get('/files/group/{category}', 'FileController@category')
    ->name('files.category')
    ->middleware('can:browse,category');
Route::get('/files/view/{file}', 'FileController@show')
    ->name('files.show')
    ->middleware('can:view,file');
Route::get('/files/download/{file}', 'FileController@download')
    ->name('files.download')
    ->middleware('can:download,file');

// Activity (examples)
Route::view('/event', 'event.index');
Route::view('/event/single', 'event.single');
Route::view('/files/single', 'files.single');

// Join us
Route::get('/join', 'JoinController@index')->name('join');
Route::post('/join', 'JoinController@submit');
Route::get('/join/welcome', 'JoinController@after')->name('join.complete');

// Authentication and forgotten passwords
Route::prefix('auth')->group(function () {
    $this->auth([
        'verify' => false,
        'register' => false
    ]);
});

// My account
Route::prefix('me')->name('user.')->middleware('auth')->group(function () {
    $this->get('/', 'UserController@index')->name('home');
    $this->get('/info', 'UserController@view')->name('info');
    $this->patch('/info', 'UserController@update');
});

// Admin panel
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'permission:admin'])
    ->namespace('Admin')
    ->group(function () {
        $this->redirect('/', '/admin/home');
        $this->get('home', 'HomeController@index')->name('home');
        $this->get('members', 'MemberController@index')->name('members');
        $this->get('events', 'EventController@index')->name('events');

        // WordPress login
        $this->get('wordpress', 'WordPressController@index')->middleware(['permission:content'])->name('wordpress');
        $this->post('wordpress', 'WordPressController@login')->middleware(['permission:content']);

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
        Route::prefix('join')
            ->name('join.')
            ->middleware('permission:join.manage')
            ->group(function () {
                // Index page, optionally per category
                $this->get('/', 'FileController@index')
                    ->name('index');

                // Category overview
                $this->get('/category/{category}', 'FileController@list')
                    ->name('list');

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

                // Deletion request
                $this->delete('/file/{file}', 'FileController@delete')
                    ->name('delete')
                    ->middleware('can:delete,file');

                // Download request
                Route::get('/download/{file}', 'FileController@download')
                    ->name('download')
                    ->middleware('can:download,file');
            });
    });

// WordPress fallback
Route::fallback('WordPressController@fallback');
