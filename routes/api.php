<?php

declare(strict_types=1);

use App\Http\Controllers\Api;
use App\Http\Controllers\TelegramBotController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Old plazacam routes
Route::addRoute(['GET', 'PUT'], 'plazacam/{user}/{webcam}', fn () => Response::json([
    'success' => 0,
    'error' => [
        'message' => 'This route is deprecated, use the new Webcam API instead',
    ],
], 400));

Route::get('/minisite/{domain}/config', [Api\MinisiteController::class, 'config'])->name('minisite.config');
Route::get('/minisite/{domain}/sitemap', [Api\MinisiteController::class, 'sitemap'])->name('minisite.sitemap');
Route::get('/minisite/{domain}/page/{page}', [Api\MinisiteController::class, 'showPage'])->name('minisite.page')
    ->where('page', '[a-z0-9][a-z0-9-/]*');

Route::middleware(['auth:sanctum', 'member'])->group(function () {
    Route::name('webcam.')->prefix('/webcam/')->group(function () {
        Route::get('/{camera}', [Api\WebcamController::class, 'show'])->name('show');
        Route::put('/', [Api\WebcamController::class, 'update'])->name('update');
    });

    Route::get('/quotes', [Api\BotQuoteController::class, 'index'])->name('quotes.list');
    Route::get('/quotes/all', [Api\BotQuoteController::class, 'indexAll'])->name('quotes.list-all');
    Route::get('/quotes/book', [Api\BotQuoteController::class, 'book'])->name('quotes.book');
});

// Webhooks and frequently polling don't have a rate limit
Route::withoutMiddleware([\Illuminate\Routing\Middleware\ThrottleRequests::class . ':api'])->group(function () {
    // Register Telegram webhooks
    Route::post('/bots/telegram', [TelegramBotController::class, 'handle'])->name('bots.telegram');

    // Register Mollie webhook URL
    Route::post('/webhooks/mollie', [Api\WebhookController::class, 'mollie'])->name('webhooks.mollie');

    // Register ical route
    Route::get('/user-calendar/{user}', [Api\CalendarController::class, 'show'])->name('calendar.show');

    // Register Google Wallet webhook URL
    Route::post('/webhooks/google-wallet', [Api\WebhookController::class, 'googleWallet'])->name('webhooks.google-wallet');
});

// Register resource routes, all protected
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('activities', 'Api\ActivityController')
        ->only(['index', 'show']);
    Route::apiResource('enrollments', 'Api\EnrollmentController')
        ->only(['index', 'show']);
});
