<?php

declare(strict_types=1);

use App\Http\Controllers\Api;
use App\Http\Controllers\TelegramBotController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Webhook Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes used for webhooks. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "webhooks" middleware group. Endpoints in this file
| are not rate limited by default. You can rate limit them yourself by
| adding the throttle middleware to the group as shown below.
|
*/

// Register Telegram webhooks
Route::post('/bots/telegram', [TelegramBotController::class, 'handle'])->name('bots.telegram');

// Register Mollie webhook URL
Route::post('/webhooks/mollie', [Api\WebhookController::class, 'mollie'])->name('webhooks.mollie');

// Register ical route
Route::get('/user-calendar/{user}', [Api\CalendarController::class, 'show'])->name('calendar.show');

// Register Google Wallet webhook URL
Route::post('/webhooks/google-wallet', [Api\WebhookController::class, 'googleWallet'])->name('webhooks.google-wallet');
