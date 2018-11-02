<?php

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

// Home
Route::get('/', 'WordPressController@homepage')->name('homepage');

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
Route::get('/files/view/{file}', 'FileController@file')
    ->name('files.show')
    ->middleware('can:view,file');
Route::get('/files/download/{file}', 'FileController@file')
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

// Authentication
Route::prefix('auth')->name('auth.')->namespace('Auth')->group(function () {
        // Authentication Routes...
        $this->get('login', 'LoginController@showLoginForm')->name('login');
        $this->post('login', 'LoginController@login');
        $this->post('logout', 'LoginController@logout')->name('logout');

        // Password Reset Routes...
        Route::prefix('password')->name('password.')->group(function () {
            $this->get('reset', 'ForgotPasswordController@showLinkRequestForm')->name('request');
            $this->post('email', 'ForgotPasswordController@sendResetLinkEmail')->name('email');
            $this->get('reset/{token}', 'ResetPasswordController@showResetForm')->name('reset');
            $this->post('reset', 'ResetPasswordController@reset');
        });
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

        /**
         * File / Document system. Handles files *and* categories
         */
        Route::prefix('files')
            ->name('files.')
            ->middleware('permission:manage,App\File')
            ->group(function () {
                // Index page, optionally per category
                $this->get('/', 'FileController@index')->name('index');

                // Category overview
                $this->get('/category/{category}', 'FileController@list')->name('list');

                // Uploads
                $this->post('/upload/{category?}', 'FileController@upload')->name('upload');

                // View and edit
                $this->get('/file/{category?}/{file}', 'FileController@show')->name('show');
                $this->put('/file/{category?}/{file}', 'FileController@edit')->name('edit');

                // Publish or un-publish
                $this->patch('/file/{category?}/{file}/publish', 'FileController@publish')->name('publish');

                // Deletion request
                $this->delete('/file/{category?}/{file}', 'FileController@delete')->name('delete');
            });
    });

// WordPress fallback
Route::fallback('WordPressController@fallback');
