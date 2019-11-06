<?php

use Illuminate\Http\Request;

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

Route::middleware('wordpress')->prefix('wordpress/')->name('wordpress.')->group(function () {
    Route::get('menu', 'WordPressController@menu')->name('menu');
});

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
