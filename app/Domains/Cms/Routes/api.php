<?php

use App\Domains\Cms\Http\Controllers\ProductCategoryController;
use App\Domains\Cms\Http\Controllers\ProductController;
use App\Domains\Cms\Http\Controllers\ProductTypeController;
use App\Domains\Cms\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

Route::prefix('c')->group(function () {
    require __DIR__ . '/auth.php';

    Route::middleware(['auth:cms'])->group(function () {
        Route::apiResource('products', ProductController::class);
        Route::apiResource('categories', ProductCategoryController::class);
        Route::apiResource('types', ProductTypeController::class);
        Route::apiResource('vendors', VendorController::class);
    });
});
