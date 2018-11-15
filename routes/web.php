<?php

use Illuminate\Support\Facades\Auth;

/**
 * WEB ROUTES
 *
 * Here is where you can register web routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * contains the "web" middleware group. Now create something great!
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

// WordPress fallback
Route::fallback('WordPressController@fallback');
