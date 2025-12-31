<?php

use App\Http\Controllers\Api\Auth\GoogleSocialiteStudentController as APIAuthGoogleSocialiteStudentController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Student\DashboardController as ApiDashboardController;
use App\Http\Controllers\Api\Student\DoItOnlineController;
use App\Http\Controllers\Api\Student\ModuleListController;
use App\Http\Controllers\Api\Student\ResultController;

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
// Route::post('/create-payment-intent', function (\Illuminate\Http\Request $request) {
//     Stripe::setApiKey(env('STRIPE_SECRET'));

//     $intent = PaymentIntent::create([
//         'amount' => $request->amount,
//         'currency' => $request->currency,
//         'automatic_payment_methods' => ['enabled' => true],
//     ]);

//     return response()->json(['clientSecret' => $intent->client_secret]);
// });

Route::prefix('/v1')->name('api.')->group(function() {    

    // Test route to check StudentUser authentication via student guard
    Route::middleware('auth:student-api')->get('/profile', function (Request $request) {
        return $request->user(); // This will be a StudentUser instance
    });
    Route::post('/login', [LoginController::class, 'login']);


Route::get('auth/google', [APIAuthGoogleSocialiteStudentController::class, 'redirectToGoogleAPI']);
Route::get('auth/google/callback', [APIAuthGoogleSocialiteStudentController::class, 'handleGoogleCallbackAPI']);



    // Protected routes (require Bearer Token)
    Route::middleware('auth.api:student-api')->prefix('student')->group(function() {

        Route::controller(ApiDashboardController::class)->group(function() {
            Route::get('dashboard', 'index')->name('user.dashboard');
        });

        Route::controller(ResultController::class)->group(function() {
            Route::get('results', 'index')->name('user.results');
        });

        Route::controller(DoItOnlineController::class)->group(function() {
            Route::get('do-it-online/forms', 'formsList')->name('user.doitonline.forms');
        });

        Route::controller(ModuleListController::class)->group(function() {
            Route::get('modules', 'index')->name('user.modules.list');
        });

        Route::post('/logout', [LoginController::class, 'logout']);
    });
});