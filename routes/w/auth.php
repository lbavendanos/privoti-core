<?php

use App\Http\Controllers\W\Auth\AddressController;
use App\Http\Controllers\W\Auth\AuthController;
use App\Http\Controllers\W\Auth\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

Route::prefix('auth')->group(function () {

    Route::get('/csrf-cookie', [CsrfCookieController::class, 'show']);

    Route::middleware(['guest'])->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [AuthController::class, 'destroy']);

        Route::get('/user', [UserController::class, 'index']);
        Route::post('/user/email/notification', [UserController::class, 'sendEmailVerificationNotification'])
            ->middleware(['throttle:6,1']);
        Route::get('/user/email/verify/{id}/{hash}', [UserController::class, 'verifyEmail'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('auth.user.email.verify');

        Route::middleware(['verified'])->group(function () {
            Route::put('/user', [UserController::class, 'update']);
            Route::post('/user/password', [UserController::class, 'updatePassword']);
            Route::post('/user/email/new/notification', [UserController::class, 'sendEmailChangeVerificationNotification'])
                ->middleware(['throttle:6,1']);
            Route::get('/user/email/new/verify/{id}/{email}/{hash}', [UserController::class, 'verifyNewEmail'])
                ->middleware(['signed', 'throttle:6,1'])
                ->name('auth.user.email.new.verify');

            Route::apiResource('/addresses', AddressController::class);
            Route::put('/addresses/{address}/default', [AddressController::class, 'setDefault']);
        });
    });
});
