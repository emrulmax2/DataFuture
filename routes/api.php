<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Stripe\Stripe;
use Stripe\PaymentIntent;

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

Route::post('/create-payment-intent', function (\Illuminate\Http\Request $request) {
    Stripe::setApiKey(env('STRIPE_SECRET'));

    $intent = PaymentIntent::create([
        'amount' => $request->amount,
        'currency' => $request->currency,
        'automatic_payment_methods' => ['enabled' => true],
    ]);

    return response()->json(['clientSecret' => $intent->client_secret]);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
