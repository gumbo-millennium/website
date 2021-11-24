<?php

declare(strict_types=1);

use App\Http\Controllers;
use App\Http\Controllers\Admin as AdminControllers;
use App\Http\Controllers\EnrollNew\EnrollmentController;
use App\Http\Controllers\FileExportController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\LustrumController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\Shop;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

$loginCsp = vsprintf('%s:%s', [
    Spatie\Csp\AddCspHeaders::class,
    App\Http\Policy\LoginPolicy::class,
]);

// Bind redirects as very, very first.
foreach (Config::get('gumbo.redirect-domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', [RedirectController::class, 'index']);
        Route::get('/{slug}', [RedirectController::class, 'redirect'])
            ->where('slug', '.+');
    });
}

// Bind Lustrum minisite
foreach (Config::get('gumbo.lustrum-domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', [LustrumController::class, 'index']);
        Route::get('/{any}', [LustrumController::class, 'other'])
            ->where('any', '.+');
    });
}

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
 * Plazacam routes.
 */
Route::get('plazacam/{image}', 'PlazaCamController@image')
    ->middleware(['auth', 'member'])
    ->name('plazacam');

/**
 * Admin routes.
 */
Route::prefix('admin/')->group(function () {
    Route::get('download-export/{export}', [FileExportController::class, 'download'])
        ->name('export.download');

    Route::get('mollie/enrollments/{enrollment}', [AdminControllers\MollieRedirectController::class, 'enrollment'])
        ->name('admin.mollie.enrollments');

    Route::get('mollie/orders/{order}', [AdminControllers\MollieRedirectController::class, 'order'])
        ->name('admin.mollie.orders');
});

/**
 * Files route.
 */
Route::middleware(['auth', 'member'])->prefix('bestanden')->name('files.')->group(static function () {
    // Main route
    Route::get('/', 'FileController@index')->name('index');

    // Search
    Route::get('/zoeken', 'FileController@search')->name('search');

    // Subcategory route
    Route::get('/{category}', 'FileController@category')->name('category');

    // Single file view
    Route::get('/bestand/{bundle}', 'FileController@show')->name('show');

    // Download views
    Route::get('/download/{bundle}', 'FileController@download')->name('download');
    Route::get('/download-single/{media?}', 'FileController@downloadSingle')->name('download-single');
});

/**
 * Activities.
 */
Route::prefix('activiteiten')->name('activity.')->group(static function () {
    // USER ROUTES
    // Main route
    Route::get('/', 'Activities\\DisplayController@index')->name('index');

    // Single view
    Route::get('/{activity}', 'Activities\\DisplayController@show')->name('show');

    // Login route
    Route::get('/{activity}/login', 'Activities\\DisplayController@login')->name('login');

    // Re-confirm route
    Route::post('/{activity}/verify-email', 'Activities\\DisplayController@retryActivate')->name('verify-email');
});
// Fix sometimes linking to /activities
Route::permanentRedirect('/activities', '/activiteiten');
Route::permanentRedirect('/activity', '/activiteiten');
Route::permanentRedirect('/activiteit', '/activiteiten');

/**
 * Enrollments.
 */
Route::prefix('/activiteiten/{activity}/inschrijven')->name('enroll.')->middleware(['auth'])->group(function () {
    Route::get('/', [EnrollmentController::class, 'show'])->name('show');

    // Create basic enrollment
    Route::get('/ticket', 'EnrollNew\\EnrollmentController@create')->name('create');
    Route::post('/ticket', 'EnrollNew\\EnrollmentController@store')->name('store');

    // Answer form questions
    Route::get('/form', 'EnrollNew\\EnrollmentController@form')->name('form');
    Route::post('/form', 'EnrollNew\\EnrollmentController@formStore')->name('formStore');

    // Enter payment info
    Route::get('/pay', 'EnrollNew\\EnrollmentController@pay')->name('pay');
    Route::post('/pay', 'EnrollNew\\EnrollmentController@payStore')->name('payStore');
    // Enter payment info
    Route::get('/pay/return', 'EnrollNew\\EnrollmentController@payReturn')->name('payReturn');
    Route::get('/pay/verify', 'EnrollNew\\EnrollmentController@payVerify')->name('payVerify');

    // Transfer form
    Route::get('/overdragen', 'Activities\\TransferController@sender')->name('transfer');

    // Transfer actions
    Route::post('/overdragen', 'Activities\\TransferController@senderUpdate');
    Route::delete('/overdragen', 'Activities\\TransferController@senderRemove');

    // Transfer acceptance form
    Route::get('/overnemen/{token}', 'Activities\\TransferController@receiver')->name('transfer-view');
    Route::post('/overnemen/{token}', 'Activities\\TransferController@receiverTake');
});

/**
 * News.
 */
Route::prefix('nieuws')->name('news.')->group(static function () {
    // Main route
    Route::get('/', 'NewsController@index')->name('index');

    // Single view
    Route::get('/{news}', 'NewsController@show')->name('show');
});

/**
 * Join controller.
 */
Route::prefix('word-lid')->name('join.')->group(static function () {
    // Join form (normal and intro)
    Route::get('/', 'JoinController@index')->name('form');
    Route::get('/intro', 'JoinController@index')->name('form-intro');

    // Submit button
    Route::post('/submit', 'JoinController@submit')->name('submit');

    // Post-join
    Route::get('/welkom', 'JoinController@complete')->name('complete');
});

// Authentication and forgotten passwords
Route::prefix('auth')->middleware([$loginCsp, 'no-cache', 'no-sponsor'])->group(static function () {
    Route::auth(['verify' => true]);

    // Register privacy
    Route::get('/register/privacy', 'Auth\RegisterController@showPrivacy')->name('register.register-privacy');
    Route::post('/register/privacy', 'Auth\RegisterController@savePrivacy');
});

// My account
Route::prefix('mijn-account')->name('account.')->middleware('auth', 'no-cache')->group(static function () {
    // Home
    Route::get('/', 'Account\DisplayController@index')->name('index');

    // Urls
    Route::get('/api-urls', 'Account\DisplayController@showUrls')->name('urls');

    // Edit profile
    Route::get('/bewerk-profiel', 'Account\DetailsController@editDetails')->name('edit');
    Route::patch('/bewerk-profiel', 'Account\DetailsController@updateDetails')->name('update');

    // Quotes
    Route::get('/wist-je-datjes', 'Account\BotQuoteController@index')->name('quotes');
    Route::delete('/wist-je-datjes', 'Account\BotQuoteController@destroy')->name('quotes.delete');

    // Permissions
    Route::get('/toestemmingen', 'Account\GrantsController@editGrants')->name('grants');
    Route::post('/toestemmingen', 'Account\GrantsController@updateGrants');

    // Telegram
    Route::get('/telegram/connect', 'Account\TelegramController@create')->name('tg.link');
    Route::post('/telegram/connect', 'Account\TelegramController@store');
    Route::delete('/telegram/disconnect', 'Account\TelegramController@delete')->name('tg.unlink');

    // Data Exports
    Route::get('/inzageverzoek', [Controllers\Account\DataExportController::class, 'index'])->name('export.index');
    Route::post('/inzageverzoek', [Controllers\Account\DataExportController::class, 'store'])->name('export.store');
    Route::get('/inzageverzoek/{id}/{token}', [Controllers\Account\DataExportController::class, 'show'])->name('export.show');
    Route::get('/inzageverzoek/{id}/{token}/download', [Controllers\Account\DataExportController::class, 'download'])->name('export.download');
});

// Onboarding URLs
Route::prefix('onboarding')->name('onboarding.')->middleware('auth')->group(static function () {
    Route::get('/welcome', 'Auth\\RegisterController@afterRegister')->name('new-account');
});

// Sponsors
Route::prefix('sponsoren')->name('sponsors.')->middleware('no-sponsor')->group(static function () {
    Route::get('/', 'SponsorController@index')->name('index');
    Route::get('/{sponsor}', 'SponsorController@show')->name('show');
    Route::get('/{sponsor}/website', 'SponsorController@redirect')->name('link');
});

/**
 * Webshop.
 */
Route::prefix('shop')->name('shop.')->middleware(['auth', 'member'])->group(static function () {
    // Homepage
    Route::get('/', [Shop\ProductController::class, 'index'])->name('home');

    // Single item display
    Route::get('/item/{product}', [Shop\ProductController::class, 'showProduct'])->name('product');
    Route::get('/item/{product}/{variant}', [Shop\ProductController::class, 'showProductVariant'])->name('product-variant');

    // Shopping cart
    Route::get('/winkelwagen', [Shop\CartController::class, 'index'])->name('cart');
    Route::post('/winkelwagen', [Shop\CartController::class, 'add'])->name('cart.add');
    Route::patch('/winkelwagen', [Shop\CartController::class, 'update'])->name('cart.update');

    Route::get('/plaats-bestelling', [Shop\OrderController::class, 'create'])->name('order.create');
    Route::post('/plaats-bestelling', [Shop\OrderController::class, 'store'])->name('order.store');

    Route::get('/bestellingen', [Shop\OrderController::class, 'index'])->name('order.index');

    Route::get('/bestellingen/{order}', [Shop\OrderController::class, 'show'])->name('order.show');

    Route::get('/bestellingen/{order}/betalen', [Shop\OrderController::class, 'pay'])->name('order.pay');
    Route::get('/bestellingen/{order}/betalen/go', [Shop\OrderController::class, 'payRedirect'])->name('order.pay-redirect');
    Route::get('/bestellingen/{order}/betalen/back', [Shop\OrderController::class, 'payReturn'])->name('order.pay-return');

    Route::get('/bestellingen/{order}/annuleren', [Shop\OrderController::class, 'cancelShow'])->name('order.cancel');
    Route::post('/bestellingen/{order}/annuleren', [Shop\OrderController::class, 'cancel']);

    // Category
    Route::get('/{category}', [Shop\ProductController::class, 'showCategory'])->name('category');
});

// Common mistakes handler
Route::redirect('/sign-up', '/word-lid');
Route::redirect('/join', '/word-lid');

// Styling pages
if (app()->isLocal()) {
    Route::view('/test/colors', 'tests.colors');
    Route::view('/test/loading', 'tests.loading');
}

// Page groups
$groupRegex = sprintf(
    '^(%s)$',
    implode('|', array_map(
        static fn ($key) => preg_quote($key, '/'),
        array_keys(config('gumbo.page-groups')),
    )),
);
Route::get('{group}', 'PageController@group')->where('group', $groupRegex)->name('group.index');
Route::get('{group}/{slug}', 'PageController@groupPage')->where('group', $groupRegex)->name('group.show');

// Redirects
Route::redirect('corona', '/coronavirus');
Route::redirect('covid', '/coronavirus');

// Images
Route::get('/img/{path}', [ImageController::class, 'render'])->name('image.render')->where('path', '.+');

// Page fallback
Route::fallback('PageController@fallback');
