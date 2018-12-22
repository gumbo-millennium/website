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

// Plazacam
Route::get('plazacam/{image}', 'PlazaCamController@image')
    ->middleware(['auth', 'member'])
    ->name('plazacam');

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
Route::view('/events', 'events.index')->name('events.home');
Route::view('/events/single', 'events.single')->name('events.show');

// News (examples)
Route::view('/news', 'news.index')->name('news.home');
Route::view('/news/single', 'news.single')->name('news.show');

// Join us
Route::get('/join', 'JoinController@index')->name('join');
Route::post('/join', 'JoinController@submit');
Route::get('/join/welcome', 'JoinController@complete')->name('join.complete');

// Authentication and forgotten passwords
Route::prefix('auth')->group(function () {
    $this->auth([
        'verify' => true,
        'register' => true
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
