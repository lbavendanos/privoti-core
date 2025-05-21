<?php

use App\Domains\Cms\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

Route::prefix('c')->group(function () {

    Route::get('csrf-cookie', [CsrfCookieController::class, 'show']);

    Route::middleware(['guest:cms'])->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
    });

    Route::middleware(['auth:cms'])->group(function () {
        Route::post('logout', [AuthController::class, 'destroy']);
    });
});
