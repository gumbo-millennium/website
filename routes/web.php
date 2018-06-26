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

// Login
Route::get('/auth', function () {
    return view('auth.login');
});

Route::get('/auth/reset', function () {
    return view('auth.reset');
});
