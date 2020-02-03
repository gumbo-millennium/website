<?php

declare(strict_types=1);

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
Route::get('/nieuws', 'NewsController@index')->name('news.index');
Route::get('/nieuws/{item}', 'NewsController@show')->name('news.show');

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
Route::middleware('auth')->prefix('bestanden')->name('files.')->group(static function () {
    // Main route
    Route::get('/', 'FileController@index')->name('index');

    // Subcategory route
    Route::get('/{category}', 'FileController@category')->name('category');

    // Single file view
    Route::get('/bestand/{file}', 'FileController@show')->name('show');

    // Download view
    Route::get('/download/{file}', 'FileController@download')->name('download');
});

/**
 * Activities
 */
Route::prefix('activiteiten')->name('activity.')->group(static function () {
    // USER ROUTES
    // Main route
    Route::get('/', 'Activities\\DisplayController@index')->name('index');

    // Single view
    Route::get('/{activity}', 'Activities\\DisplayController@show')->name('show');

    // Single view
    Route::get('/{activity}/login', 'Activities\\DisplayController@login')->name('login');
});
// Fix sometimes linking to /activities
Route::permanentRedirect('/activities', '/activiteiten');
Route::permanentRedirect('/activity', '/activiteiten');
Route::permanentRedirect('/activiteit', '/activiteiten');

/**
 * Enrollments
 */
Route::prefix('activiteiten/{activity}/inschrijven')->name('enroll.')->group(static function () {
    // Actioon view
    Route::get('/', 'Activities\\TunnelController@get')->name('show');

    // Enroll start
    Route::post('/', 'Activities\\EnrollmentController@create')->name('create');

    // Enroll form
    Route::patch('/', 'Activities\\FormController@save')->name('edit');

    // Enroll payment start
    Route::post('/betaling', 'Activities\\PaymentController@store')->name('pay');

    // Enroll payment start
    Route::get('/betaling', 'Activities\\PaymentController@start')->name('pay-wait');

    // Enroll payment complete
    Route::get('/betaling/afronden', 'Activities\\PaymentController@complete')->name('pay-return');

    // Enroll form
    Route::get('/uitschrijven', 'Activities\\EnrollmentController@delete')->name('remove');

    // Enroll form (do)
    Route::delete('/uitschrijven', 'Activities\\EnrollmentController@destroy');
});

/**
 * News
 */
Route::prefix('nieuws')->name('news.')->group(static function () {
    // Main route
    Route::get('/', 'NewsController@index')->name('index');

    // Single view
    Route::get('/{news}', 'NewsController@show')->name('show');
});

/**
 * Join controller
 */
Route::prefix('word-lid')->name('join.')->group(static function () {
    // Join form
    Route::get('/', 'JoinController@index')->name('form');

    // Submit button
    Route::post('/submit', 'JoinController@submit')->name('submit');

    // Post-join
    Route::get('/welkom', 'JoinController@complete')->name('complete');
});


// Authentication and forgotten passwords
Route::prefix('auth')->middleware($loginCsp)->group(static function () {
    Route::auth(['verify' => true]);

    // Register privacy
    Route::get('/register/privacy', 'Auth\RegisterController@showPrivacy')->name('register.register-privacy');
    Route::post('/register/privacy', 'Auth\RegisterController@savePrivacy');
});

// My account
Route::prefix('mijn-account')->name('account.')->middleware('auth')->group(static function () {
    Route::get('/', 'AccountController@index')->name('index');
    Route::get('/profiel', 'AccountController@edit')->name('edit');
    Route::patch('/profiel', 'AccountController@update')->name('update');
});

// Onboarding URLs
Route::prefix('onboarding')->name('onboarding.')->middleware('auth')->group(static function () {
    Route::get('/welcome', 'Auth\\RegisterController@afterRegister')->name('new-account');
});

// Common mistakes handler
Route::redirect('/sign-up', '/word-lid');
Route::redirect('/join', '/word-lid');

// Page fallback
Route::fallback('PageController@fallback');
