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
Route::get('/files', 'FileController@index')->name('files.index');
Route::get('/files/group/{slug}', 'FileController@category')->name('files.category');
Route::get('/files/view/{slug}', 'FileController@post')->name('files.single');

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
Route::prefix('admin')->name('admin.')->middleware('auth')->namespace('Admin')->group(function () {
    $this->redirect('/', '/admin/home');
    $this->get('home', 'HomeController@index')->name('admin.home');
    $this->get('members', 'MemberController@index')->name('admin.members');
    $this->get('events', 'EventController@index')->name('admin.events');

    Route::prefix('files')->name('files.')->group(function () {
        $this->get('/', 'FileController@index')->name('index');
        $this->post('/', 'FileController@index')->name('add');
        $this->put('/', 'FileController@index')->name('edit');
        $this->delete('/', 'FileController@index')->name('delete');
    });
});

// WordPress fallback
Route::fallback('WordPressController@fallback');
