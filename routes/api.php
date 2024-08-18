<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/auth/user', [UserController::class, 'index']);

    Route::middleware(['verified'])->group(function () {
        Route::prefix('auth')->group(function () {
            Route::put('/user', [UserController::class, 'update']);
            Route::post('/user/password', [UserController::class, 'updatePassword']);
            Route::post('/user/email/notification', [UserController::class, 'sendEmailChangeVerificationNotification']);
            Route::get('/user/email/verify/{id}/{email}/{hash}', [UserController::class, 'verifyNewEmail'])
                ->middleware(['signed', 'throttle:6,1'])
                ->name('auth.user.email.verify');

            Route::apiResource('/addresses', AddressController::class);
            Route::put('/addresses/{address}/default', [AddressController::class, 'setDefault']);
        });
    });
});
