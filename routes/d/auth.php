<?php

use App\Http\Controllers\D\Auth\AuthController;
use App\Http\Controllers\D\Auth\AdminController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

Route::prefix('auth')->group(function () {

    Route::get('/csrf-cookie', [CsrfCookieController::class, 'show']);

    Route::middleware(['guest:dashboard'])->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
    });

    Route::middleware(['auth:dashboard'])->group(function () {
        Route::post('/logout', [AuthController::class, 'destroy']);
        Route::get('/user', [AdminController::class, 'index']);
    });
});
