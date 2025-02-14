<?php

use App\Domains\Cms\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('c')->group(function () {
    require __DIR__ . '/auth.php';

    Route::middleware(['auth:cms'])->group(function () {
        Route::apiResource('products', ProductController::class);
    });
});
