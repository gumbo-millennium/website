<?php

/**
 * WEB ROUTES
 *
 * Here is where you can register web routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * contains the "web" middleware group. Now create something great!
 */

// Home and privacy policy
Route::get('/', 'PageController@homepage')->name('home');
Route::get('/privacy-policy', 'PageController@privacy')->name('privacy');

// Sitemap
Route::get('/sitemap.xml', 'SitemapController@index')->name('sitemap');

// News route
Route::get('/news', 'NewsController@index')->name('news.index');
Route::get('/news/{item}', 'NewsController@show')->name('news.show');

// Add search route
Route::get('/search', 'SearchController@index')->name('search-form');
Route::get('/search/{query}', 'SearchController@search')->name('search');

/**
 * Plazacam routes
 */
Route::get('plazacam/{image}', 'PlazaCamController@image')
    ->middleware(['auth', 'member'])
    ->name('plazacam');

/**
 * Files route
 */
Route::middleware('auth')->prefix('files')->name('files.')->group(function () {
    // Main route
    Route::get('/', 'FileController@index')->name('index');

    // Subcategory route
    Route::get('/{category}', 'FileController@category')->name('category');

    // Single file view
    Route::get('/view/{file}', 'FileController@show')->name('show');

    // Download view
    Route::get('/download/{file}', 'FileController@download')->name('download');
});

/**
 * Activities
 */
Route::prefix('activity')->name('activity.')->group(function () {
    // USER ROUTES
    // Main route
    Route::get('/', 'Activities\\DisplayController@index')->name('index');

    // Single view
    Route::get('/{activity}', 'Activities\\DisplayController@show')->name('show');

    // Single view
    Route::get('/{activity}/login', 'Activities\\DisplayController@login')->name('login');
});
// Fix sometimes linking to /activities
Route::permanentRedirect('/activities', '/activity');

/**
 * Enrollments
 */
Route::prefix('enroll')->name('enroll.')->group(function () {
    // Default route¸ redirect → my enrollments
    Route::permanentRedirect('/', '/me');

    // My enrollments
    Route::get('/me', 'Activities\\EnrollmentController@index')->name('index');

    // Enroll status view
    Route::post('/{activity}', 'Activities\\EnrollmentController@show')->name('show');

    // Enroll start
    Route::post('/{activity}/create', 'Activities\\EnrollmentController@create')->name('create');

    // Enroll update
    Route::get('/{activity}/update', 'Activities\\EnrollmentController@edit')->name('edit');

    // Enroll update
    Route::post('/{activity}/update', 'Activities\\EnrollmentController@update');

    // Enroll removal (confirm)
    Route::get('/{activity}/delete', 'Activities\\EnrollmentController@delete')->name('delete');

    // Enroll removal (perform)
    Route::delete('/{activity}/delete', 'Activities\\EnrollmentController@destroy');
});
/**
 * Enrollment payments
 */
Route::prefix('enrollments/pay')->name('payments.')->group(function () {
    // Default route¸ redirect → my enrollments
    Route::permanentRedirect('/', '/me');

    // Enroll start
    Route::post('/{activity}/start', 'Activities\\PaymentController@create')->name('start');

    // Enroll start
    Route::post('/{activity}/start', 'Activities\\PaymentController@store');

    // Enroll update
    Route::get('/{activity}/complete', 'Activities\\PaymentController@complete')->name('complete');
});

/**
 * News (through WordPress posts)
 */
Route::prefix('news')->name('news.')->group(function () {
    // Main route
    Route::get('/', 'NewsController@index')->name('index');

    // Single view
    Route::get('/{news}', 'NewsController@show')->name('show');
});

/**
 * Join controller
 */
Route::prefix('join')->name('join.')->group(function () {
    // Join form
    Route::get('/', 'JoinController@index')->name('form');

    // Submit button
    Route::post('/send', 'JoinController@submit')->name('submit');

    // Post-join
    Route::get('/welcome', 'JoinController@complete')->name('complete');
});

// Authentication and forgotten passwords
Route::prefix('auth')->group(function () {
    Route::auth([
        'verify' => true,
        'register' => true
    ]);
});

// My account
Route::prefix('me')->name('user.')->middleware('auth')->group(function () {
    Route::get('/', 'UserController@index')->name('home');
    Route::get('/info', 'UserController@view')->name('info');
    Route::patch('/info', 'UserController@update');
});

// Common mistakes handler
Route::redirect('/sign-up', '/join');

// Page fallback
Route::fallback('PageController@fallback');

// LEGACY REDIRECTS
Route::get('/nova', 'LegacyController@gone');
