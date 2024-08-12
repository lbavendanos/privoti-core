<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/auth/user', [UserController::class, 'index']);

    Route::middleware(['verified'])->group(function () {
        Route::put('/auth/user', [UserController::class, 'update']);
    });
});
