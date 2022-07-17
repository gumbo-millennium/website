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

Route::middleware(['auth:sanctum', 'member'])->name('api.webcam.')->prefix('/webcam/')->group(function () {
    Route::get('/{camera}', [Api\WebcamController::class, 'show'])->name('show');
    Route::put('/', [Api\WebcamController::class, 'update'])->name('update');
});

// Register Telegram webhooks
Route::post('/bots/telegram', [TelegramBotController::class, 'handle'])->name('bots.telegram');

// Register Mollie webhook URL
Route::post('/webhooks/mollie', [Api\WebhookController::class, 'mollie'])->name('webhooks.mollie');

// Register ical route
Route::get('/user-calendar/{user}', [Api\CalendarController::class, 'show'])->name('calendar.show');
