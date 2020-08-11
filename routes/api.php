<?php

declare(strict_types=1);

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

// Register API for Stripe endpoints
Route::stripeWebhooks('payments/stripe/handle');

// Register Botman
Route::match(['get', 'post'], '/botman', 'BotManController@handle')->name('botman');

// Register bot routes
Route::name('bots.')->prefix('/bots/')->group(static function () {
    // Register quotes API (SIGNED URLS!)
    Route::name('quotes.')->prefix('/quotes/')->group(static function () {
        // Submits a new quote
        Route::post('/', 'Api\\BotQuoteController@submit')->name('submit');

        // Retrieves quotes for the given user
        Route::get('/{user}', 'Api\\BotQuoteController@list')->name('show');
    });
});
