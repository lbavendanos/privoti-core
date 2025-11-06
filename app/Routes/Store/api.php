<?php

declare(strict_types=1);

use App\Http\Store\Controllers\Auth\AddressController;
use App\Http\Store\Controllers\Auth\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('s')->group(function (): void {

    Route::prefix('auth')->group(function (): void {

        Route::middleware(['auth:store'])->group(function (): void {
            Route::get('/user', [UserController::class, 'index']);
            Route::post('/user/email/notification', [UserController::class, 'sendEmailVerificationNotification'])
                ->middleware(['throttle:6,1']);
            Route::get('/user/email/verify/{id}/{hash}', [UserController::class, 'verifyEmail'])
                ->middleware(['signed', 'throttle:6,1'])
                ->name('auth.customer.email.verify');

            Route::middleware(['verified'])->group(function (): void {
                Route::put('/user', [UserController::class, 'update']);
                Route::post('/user/password', [UserController::class, 'updatePassword']);
                Route::post('/user/email/new/notification', [UserController::class, 'sendEmailChangeVerificationNotification'])
                    ->middleware(['throttle:6,1']);
                Route::get('/user/email/new/verify/{id}/{email}/{hash}', [UserController::class, 'verifyNewEmail'])
                    ->middleware(['signed', 'throttle:6,1'])
                    ->name('auth.customer.email.new.verify');

                Route::apiResource('/addresses', AddressController::class);
                Route::put('/addresses/{address}/default', [AddressController::class, 'setDefault']);
            });
        });
    });
});
