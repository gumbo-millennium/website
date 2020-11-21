<?php

declare(strict_types=1);

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

// Plazacam submission
Route::put('plazacam/{user}/{image}', 'PlazaCamController@store')
    ->middleware('signed')
    ->name('plazacam.store');

// Plazacam viewing via API
Route::get('plazacam/{user}/{image}', 'PlazaCamController@api')
    ->middleware('signed')
    ->name('plazacam.view');

// Register API for payment endpoints
Route::stripeWebhooks('payments/stripe/handle');
Route::post('payments/mollie/handle', 'MollieWebhookController@handle')->name('mollie.webhook');

// Register Telegram webhooks
Route::post('/bots/telegram', 'TelegramBotController@handle')->name('bots.telegram');
