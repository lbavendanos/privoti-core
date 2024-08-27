<?php

use App\Domains\D\Http\Controllers\Auth\AuthController;
use App\Domains\D\Http\Controllers\Auth\AdminController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

Route::prefix('auth')->group(function () {

    Route::get('/csrf-cookie', [CsrfCookieController::class, 'show']);

    Route::middleware(['guest:dashboard'])->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
    });

    Route::middleware(['auth:dashboard'])->group(function () {
        Route::post('/logout', [AuthController::class, 'destroy']);
        Route::get('/admin', [AdminController::class, 'index']);

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
