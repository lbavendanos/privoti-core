<?php

use App\Domains\Cms\Http\Controllers\Auth\AuthController;
use App\Domains\Cms\Http\Controllers\Auth\AdminController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

Route::prefix('auth')->group(function () {

    Route::get('/csrf-cookie', [CsrfCookieController::class, 'show']);

    Route::middleware(['guest:cms'])->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    Route::middleware(['auth:cms'])->group(function () {
        Route::post('/logout', [AuthController::class, 'destroy']);

        Route::get('/admin', [AdminController::class, 'index']);
        Route::post('/admin/email/notification', [AdminController::class, 'sendEmailVerificationNotification'])
            ->middleware(['throttle:6,1']);
        Route::get('/admin/email/verify/{id}/{hash}', [AdminController::class, 'verifyEmail'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('auth.admin.email.verify');

        Route::middleware(['verified'])->group(function () {
            Route::put('/admin', [AdminController::class, 'update']);
            Route::post('/admin/password', [AdminController::class, 'updatePassword']);
            Route::post('/admin/email/new/notification', [AdminController::class, 'sendEmailChangeVerificationNotification'])
                ->middleware(['throttle:6,1']);
            Route::get('/admin/email/new/verify/{id}/{email}/{hash}', [AdminController::class, 'verifyNewEmail'])
                ->middleware(['signed', 'throttle:6,1'])
                ->name('auth.admin.email.new.verify');
        });
    });
});
