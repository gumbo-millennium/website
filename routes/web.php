<?php

declare(strict_types=1);

use App\Http\Controllers;
use App\Http\Controllers\Account;
use App\Http\Controllers\Activities;
use App\Http\Controllers\Admin as AdminControllers;
use App\Http\Controllers\Auth;
use App\Http\Controllers\EnrollNew;
use App\Http\Controllers\FileExportController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\Shop;
use App\Http\Policy;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Spatie\Csp\AddCspHeaders;

$addCsp = fn (string $csp) => sprintf('%s:%s', AddCspHeaders::class, $csp);

// Bind redirects as very, very first.
foreach (Config::get('gumbo.redirect-domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', [RedirectController::class, 'index']);
        Route::get('/{slug}', [RedirectController::class, 'redirect'])->where('slug', '.+');
    });
}

// Home
Route::get('/', [Controllers\HomepageController::class, 'show'])->name('home');

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
Route::get('plazacam/{image}', [Controllers\Api\WebcamController::class, 'show'])
    ->middleware(['auth', 'member'])
    ->name('plazacam');

/**
 * Admin routes.
 */
Route::prefix('admin/')->group(function () {
    Route::get('download-export/{export}', [FileExportController::class, 'download'])
        ->name('export.download');

    Route::get('mollie/dashboard/{payment}', [AdminControllers\MollieRedirectController::class, 'show'])
        ->name('admin.mollie.show');

    Route::get('excel/templates/import', [AdminControllers\ActivityImportController::class, 'downloadImportFormat'])
        ->name('admin.activity.import-template');

    Route::get('excel/templates/activity-barcodes/{activity}', [AdminControllers\ActivityImportController::class, 'downloadReplaceBarcodesTemplate'])
        ->name('admin.activity.replace-barcodes-template');
});

/**
 * Ticket routes.
 */
// Route::prefix('tickets')->name('tickets.')->group(function () {
//     Route::get('/', [Controllers\TicketController::class, 'index'])->name('index');
//     Route::get('/{ticket}', [Controllers\TicketController::class, 'show'])->name('show');
// });
Route::redirect('/tickets', '/mijn-account/tickets')->name('tickets.index');

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
Route::prefix('activiteiten/{activity}/inschrijven')->name('enroll.')->middleware(['auth', 'no-sponsor'])->group(function () {
    Route::get('/', [EnrollNew\EnrollmentController::class, 'show'])->name('show');

    // Create basic enrollment
    Route::get('/ticket', [EnrollNew\TicketController::class, 'create'])->name('create');
    Route::post('/ticket', [EnrollNew\TicketController::class, 'store'])->name('store');

    // Answer form questions
    Route::get('/gegevens', [EnrollNew\FormController::class, 'edit'])->name('form');
    Route::put('/gegevens', [EnrollNew\FormController::class, 'update'])->name('formStore');

    // Start payment
    Route::get('/betalen', [EnrollNew\PaymentController::class, 'create'])->name('pay');
    Route::post('/betalen', [EnrollNew\PaymentController::class, 'store'])->name('payStore');

    // Show payment loading
    Route::get('/betalen/go', [EnrollNew\PaymentController::class, 'show'])->name('payShow');

    // Redirect to Mollie or verify payment
    Route::get('/betalen/provider', [EnrollNew\PaymentController::class, 'redirect'])->name('payRedirect');
    Route::get('/betalen/controle', [EnrollNew\PaymentController::class, 'verify'])->name('payVerify');

    // Transfer form
    Route::get('/overdragen', [EnrollNew\TransferController::class, 'show'])->name('transfer');

    // Transfer actions
    Route::post('/overdragen', [EnrollNew\TransferController::class, 'store']);
    Route::delete('/overdragen', [EnrollNew\TransferController::class, 'destroy']);

    // Transfer acceptance form
    Route::get('/overnemen/{secret}', [EnrollNew\TransferController::class, 'showConsume'])->name('transfer-view');
    Route::post('/overnemen/{secret}', [EnrollNew\TransferController::class, 'storeConsume']);

    // Cancel form
    Route::post('/annuleren', [EnrollNew\CancelController::class, 'cancel'])->name('cancel');
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
Route::prefix('auth')->middleware([$addCsp(Policy\LoginPolicy::class), 'no-cache', 'no-sponsor'])->group(static function () {
    Route::auth(['verify' => true]);

    // Register privacy
    Route::get('/register/privacy', 'Auth\RegisterController@showPrivacy')->name('register.register-privacy');
    Route::post('/register/privacy', 'Auth\RegisterController@savePrivacy');

    // Logout page
    Route::get('/logged-out', [Auth\LoginController::class, 'showLoggedout'])
        ->name('logout.done');
});

// My account
Route::prefix('mijn-account')->name('account.')->middleware(['auth', 'no-cache'])->group(static function () {
    // Home
    Route::get('/', [Account\IndexController::class, 'index'])->name('index');
    Route::post('/request-update', [Account\IndexController::class, 'requestUpdate'])->name('request-update');

    // Create basic enrollment
    Route::get('/tickets', [Account\TicketController::class, 'index'])->name('tickets');
    Route::get('/tickets/{id}/download', [Account\TicketController::class, 'download'])->name('tickets.download');

    // Urls
    Route::get('/api-tokens', [Account\ApiTokenController::class, 'index'])->name('tokens.index');
    Route::post('/api-tokens', [Account\ApiTokenController::class, 'store'])->name('tokens.store');
    Route::delete('/api-tokens', [Account\ApiTokenController::class, 'destroy'])->name('tokens.destroy');
    Route::get('/api-tokens/aanmaken', [Account\ApiTokenController::class, 'create'])->name('tokens.create');

    // Edit profile
    Route::get('/profiel', [Account\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profiel', [Account\ProfileController::class, 'update'])->name('profile.update');

    // Edit password
    Route::get('/wachtwoord', [Account\PasswordController::class, 'edit'])->name('password.edit');
    Route::post('/wachtwoord', [Account\PasswordController::class, 'update'])->name('password.update');

    // Quotes
    Route::get('/wist-je-datjes', [Account\BotQuoteController::class, 'index'])->name('quotes');
    Route::delete('/wist-je-datjes', [Account\BotQuoteController::class, 'destroy'])->name('quotes.delete');

    // Permissions
    Route::get('/toestemmingen', [Account\GrantsController::class, 'edit'])->name('grants');
    Route::post('/toestemmingen', [Account\GrantsController::class, 'update']);

    // Telegram
    Route::get('/telegram', [Account\TelegramController::class, 'show'])->name('tg.show');
    Route::get('/telegram/connect', [Account\TelegramController::class, 'create'])->name('tg.link');
    Route::post('/telegram/connect', [Account\TelegramController::class, 'store']);
    Route::delete('/telegram/disconnect', [Account\TelegramController::class, 'delete'])->name('tg.unlink');

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

    Route::post('/bestellingen/{order}/betalen', [Shop\OrderController::class, 'pay'])->name('order.pay');

    Route::get('/bestellingen/{order}/annuleren', [Shop\OrderController::class, 'cancelShow'])->name('order.cancel');
    Route::post('/bestellingen/{order}/annuleren', [Shop\OrderController::class, 'cancel']);

    // Category
    Route::get('/{category}', [Shop\ProductController::class, 'showCategory'])->name('category');
});

// Payments
Route::prefix('/betalingen/{payment}')->middleware(['auth'])->group(static function () {
    Route::get('/', [Controllers\PaymentController::class, 'show'])->name('payment.show');

    Route::get('/redirect', [Controllers\PaymentController::class, 'redirect'])->name('payment.redirect');
    Route::get('/verify', [Controllers\PaymentController::class, 'verify'])->name('payment.verify');
});

/**
 * Gallery.
 */
Route::prefix('gallery')->name('gallery.')->middleware('auth')->group(function () {
    // Index
    Route::get('/', [Controllers\Gallery\AlbumController::class, 'index'])->name('index');

    // Album creation
    Route::get('/create', [Controllers\Gallery\AlbumController::class, 'create'])->name('album.create');
    Route::post('/create', [Controllers\Gallery\AlbumController::class, 'store']);

    // Photo viewing
    Route::get('/photo/{photo}/download', [Controllers\Gallery\PhotoController::class, 'download'])->name('photo.download');

    // Photo editing
    Route::get('/photo/{photo}/edit', [Controllers\Gallery\PhotoController::class, 'edit'])->name('photo.edit');
    Route::patch('/photo/{photo}/edit', [Controllers\Gallery\PhotoController::class, 'update']);
    Route::delete('/photo/{photo}/delete', [Controllers\Gallery\PhotoController::class, 'destroy'])->name('photo.delete');

    Route::get('/photo/{photo}/report', [Controllers\Gallery\PhotoController::class, 'report'])->name('photo.report');
    Route::post('/photo/{photo}/report', [Controllers\Gallery\PhotoController::class, 'storeReport']);

    Route::post('/photo/{photo}/react', [Controllers\Gallery\PhotoController::class, 'react'])->name('photo.react');

    // Album viewing (uses wildcard on second segment, must be after static URLs)
    // Also register a /:album/photo/:photo route for compat with Vue router
    Route::get('/{album}', [Controllers\Gallery\AlbumController::class, 'show'])->name('album');
    Route::get('/{album}/photo/{photo}', [Controllers\Gallery\AlbumController::class, 'show']);

    // Album uploading
    Route::get('/{album}/upload', [Controllers\Gallery\AlbumController::class, 'upload'])->name('album.upload');
    Route::post('/{album}/upload', [Controllers\Gallery\AlbumController::class, 'storeUpload']);

    // Album editing
    Route::get('/{album}/edit', [Controllers\Gallery\AlbumController::class, 'edit'])->name('album.edit');
    Route::patch('/{album}/edit', [Controllers\Gallery\AlbumController::class, 'update']);
    Route::delete('/{album}/delete', [Controllers\Gallery\AlbumController::class, 'destroy'])->name('album.delete');

    // Filepond
    Route::prefix('/filepond/{album}')->name('filepond.')->group(static function () {
        Route::post('/process', [Controllers\Gallery\FilePondController::class, 'handleProcess'])->name('process');
        Route::delete('/revert', [Controllers\Gallery\FilePondController::class, 'handleRevert'])->name('revert');
    });
});

// Barcode
Route::middleware('auth')->group(function () {
    Route::get('/scanner/', [Controllers\BarcodeController::class, 'index'])->name('barcode.index');
    Route::get('/scanner/{activity}', [Controllers\BarcodeController::class, 'show'])->name('barcode.show');
    Route::get('/api/scanner/{activity}/preload', [Controllers\BarcodeController::class, 'preload'])->name('barcode.preload');
    Route::post('/api/scanner/{activity}/consume', [Controllers\BarcodeController::class, 'consume'])->name('barcode.consume');
});

// Common mistakes handler
Route::redirect('/sign-up', '/word-lid');
Route::redirect('/join', '/word-lid');

// "Nova being weird" fix
Route::redirect('/admin/login', '/auth/login');

// Styling pages
if (App::isLocal()) {
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

// Images
Route::get('/img/{path}', [ImageController::class, 'render'])->name('image.render')->where('path', '.+');

// Page fallback
Route::fallback('PageController@fallback');
