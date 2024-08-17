<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/auth/user', [UserController::class, 'index']);

    Route::middleware(['verified'])->group(function () {
        Route::put('/auth/user', [UserController::class, 'update']);
        Route::post('/auth/user/password', [UserController::class, 'updatePassword']);
        Route::post('/auth/user/email/notification', [UserController::class, 'sendEmailChangeVerificationNotification']);
        Route::get('/auth/user/email/verify/{id}/{email}/{hash}', [UserController::class, 'verifyNewEmail'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('auth.user.email.verify');

        Route::apiResource('/auth/addresses', AddressController::class);
    });
});
