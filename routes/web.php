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
Route::get('/', function () {
    return view('index');
});

// Join
route::get('/word-lid', function () {
    abort(404, "not done yet");
})->name('join');

// Sitemap
Route::get('/sitemap.xml', 'SitemapController@index')->name('sitemap');

// About
Route::get('/about', function () {
    return view('pages.about');
});

Route::get('/about/history', function () {
    return view('pages.history');
});

Route::get('/about/board', function () {
    return view('pages.board');
});

Route::get('/about/commission', function () {
    return view('pages.commission');
});

// Blog
Route::get('/blog', function () {
    return view('blog.index');
});

Route::get('/blog/single', function () {
    return view('blog.single');
});

// Activity
Route::get('/event', function () {
    return view('event.index');
});

Route::get('/event/single', function () {
    return view('event.single');
});

// Files
Route::get('/files', function () {
    return view('files.index');
});

Route::get('/files/single', function () {
    return view('files.single');
});

Route::prefix('/auth/')->namespace('Auth')->group(function () {
        // Authentication Routes...
        $this->get('login', 'LoginController@showLoginForm')->name('auth.login');
        $this->post('login', 'LoginController@login');
        $this->post('logout', 'LoginController@logout')->name('auth.logout');

            // Password Reset Routes...
        $this->get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('auth.password.request');
        $this->post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('auth.password.email');
        $this->get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('auth.password.reset');
        $this->post('password/reset', 'ResetPasswordController@reset');
});
