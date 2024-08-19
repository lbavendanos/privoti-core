<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::middleware(['guest'])->group(function () {
        Route::post('/register', [RegisteredUserController::class, 'store']);
        Route::post('/login', [AuthenticatedSessionController::class, 'store']);
        Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);
        Route::post('/reset-password', [NewPasswordController::class, 'store']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);

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
