<?php

declare(strict_types=1);

use App\Http\Store\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

Route::prefix('s')->group(function (): void {

    Route::prefix('auth')->group(function (): void {

        Route::get('/csrf-cookie', [CsrfCookieController::class, 'show']);

        Route::middleware(['guest:store'])->group(function (): void {
            Route::post('/register', [AuthController::class, 'register']);
            Route::post('/login', [AuthController::class, 'login']);
            Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
            Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        });

        Route::middleware(['auth:store'])->group(function (): void {
            Route::post('/logout', [AuthController::class, 'destroy']);
        });
    });
});
