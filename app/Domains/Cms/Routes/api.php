<?php

use App\Domains\Cms\Http\Controllers\AuthController;
use App\Domains\Cms\Http\Controllers\CollectionController;
use App\Domains\Cms\Http\Controllers\ProductCategoryController;
use App\Domains\Cms\Http\Controllers\ProductController;
use App\Domains\Cms\Http\Controllers\ProductTypeController;
use App\Domains\Cms\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

Route::prefix('c')->group(function () {

    Route::middleware(['auth:cms'])->group(function () {
        Route::prefix('auth')->group(function () {
            Route::get('user', [AuthController::class, 'getUser']);
            Route::post('user/email/notification', [AuthController::class, 'sendEmailVerificationNotification'])
                ->middleware(['throttle:6,1']);
            Route::get('user/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
                ->middleware(['signed', 'throttle:6,1'])
                ->name('auth.user.email.verify');

            Route::middleware(['verified'])->group(function () {
                Route::put('user', [AuthController::class, 'updateUser']);
                Route::put('user/password', [AuthController::class, 'updateUserPassword']);
                Route::post('user/email/new/notification', [AuthController::class, 'sendEmailChangeVerificationNotification'])
                    ->middleware(['throttle:6,1']);
                Route::get('user/email/new/verify/{id}/{email}/{hash}', [AuthController::class, 'verifyNewEmail'])
                    ->middleware(['signed', 'throttle:6,1'])
                    ->name('auth.user.email.new.verify');
            });
        });

        Route::middleware(['verified'])->group(function () {
            Route::apiResource('collections', CollectionController::class);
            Route::delete('products', [ProductController::class, 'bulkDestroy'])->name('products.bulk-destroy');
            Route::apiResource('products', ProductController::class);
            Route::apiResource('categories', ProductCategoryController::class);
            Route::apiResource('types', ProductTypeController::class);
            Route::apiResource('vendors', VendorController::class);
        });
    });
});
