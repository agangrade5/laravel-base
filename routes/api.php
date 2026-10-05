<?php

use App\Http\Controllers\Api\v1\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.v1.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Public Authentication Routes
        |--------------------------------------------------------------------------
        */
        Route::post('register', [
            AuthController::class,
            'register'
        ])->middleware('throttle:10,1')->name('register'); //throttle = 10,1 (10 requests per minute)

        Route::post('login', [
            AuthController::class,
            'login'
        ])->middleware('throttle:10,1')->name('login'); //throttle = 10,1 (10 requests per minute)

        Route::post('send-otp', [
            AuthController::class,
            'sendOtp'
        ])->middleware('throttle:5,1')->name('send-otp'); //throttle = 5,1 (5 requests per minute)

        Route::post('verify-otp', [
            AuthController::class,
            'verifyOtp'
        ])->middleware('throttle:10,1')->name('verify-otp'); //throttle = 10,1 (10 requests per minute)

        /*
        |--------------------------------------------------------------------------
        | Protected Routes (Sanctum Bearer token)
        |--------------------------------------------------------------------------
        */
        Route::middleware('auth:sanctum')
            ->group(function () {
                Route::post('logout', [
                    AuthController::class,
                    'logout'
                ])->name('logout');
            });
    });
