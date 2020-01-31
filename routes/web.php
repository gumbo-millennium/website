<?php

/**
 * WEB ROUTES
 *
 * Here is where you can register web routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * contains the "web" middleware group. Now create something great!
 */

use Illuminate\Support\Facades\Route;

$loginCsp = vsprintf('%s:%s', [
    Spatie\Csp\AddCspHeaders::class,
    App\Http\Policy\LoginPolicy::class
]);

// Home
Route::get('/', 'PageController@homepage')->name('home');

// Sitemap
Route::get('/sitemap.xml', 'SitemapController@index')->name('sitemap');

// News route
Route::get('/news', 'NewsController@index')->name('news.index');
Route::get('/news/{item}', 'NewsController@show')->name('news.show');

// Add search route
// Route::get('/search', 'SearchController@index')->name('search-form');
// Route::get('/search/{query}', 'SearchController@search')->name('search');

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
Route::prefix('activity/{activity}/enroll')->name('enroll.')->group(function () {
    // Actioon view
    Route::get('/', 'Activities\\TunnelController@get')->name('show');

    // Enroll start
    Route::post('/', 'Activities\\EnrollmentController@create')->name('create');

    // Enroll form
    Route::patch('/', 'Activities\\FormController@save')->name('edit');

    // Enroll payment start
    Route::post('/pay', 'Activities\\PaymentController@store')->name('pay');

    // Enroll payment start
    Route::get('/pay', 'Activities\\PaymentController@start')->name('pay-wait');

    // Enroll payment complete
    Route::get('/pay/complete', 'Activities\\PaymentController@complete')->name('pay-return');

    // Enroll form
    Route::get('/delete', 'Activities\\EnrollmentController@delete')->name('remove');

    // Enroll form (do)
    Route::delete('/delete', 'Activities\\EnrollmentController@destroy');
});

/**
 * News
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
Route::prefix('auth')->middleware($loginCsp)->group(function () {
    Route::auth(['verify' => true]);

    // Register privacy
    Route::get('/register/privacy', 'Auth\RegisterController@showPrivacy')->name('register.register-privacy');
    Route::post('/register/privacy', 'Auth\RegisterController@savePrivacy');
});

// My account
Route::prefix('my-account')->name('account.')->middleware('auth')->group(function () {
    Route::get('/', 'AccountController@index')->name('index');
    Route::get('/edit', 'AccountController@edit')->name('edit');
    Route::patch('/update', 'AccountController@update')->name('update');
});

// Onboarding URLs
Route::prefix('onboarding')->name('onboarding.')->middleware('auth')->group(function () {
    Route::get('/welcome', 'Auth\\RegisterController@afterRegister')->name('new-account');
});

// Common mistakes handler
Route::redirect('/sign-up', '/join');

// Page fallback
Route::fallback('PageController@fallback');

// LEGACY REDIRECTS
Route::get('/nova', 'LegacyController@gone');
