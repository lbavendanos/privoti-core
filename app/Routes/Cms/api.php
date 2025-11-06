<?php

declare(strict_types=1);

use App\Http\Cms\Controllers\AuthController;
use App\Http\Cms\Controllers\CollectionController;
use App\Http\Cms\Controllers\CustomerAddressController;
use App\Http\Cms\Controllers\CustomerController;
use App\Http\Cms\Controllers\ProductCategoryController;
use App\Http\Cms\Controllers\ProductController;
use App\Http\Cms\Controllers\ProductTypeController;
use App\Http\Cms\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

Route::prefix('c')->group(function (): void {

    Route::middleware(['auth:cms'])->group(function (): void {
        Route::prefix('auth')->group(function (): void {
            Route::get('user', [AuthController::class, 'getUser']);
            Route::post('user/email/notification', [AuthController::class, 'sendEmailVerificationNotification'])
                ->middleware(['throttle:6,1']);
            Route::get('user/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
                ->middleware(['signed', 'throttle:6,1'])
                ->name('auth.user.email.verify');

            Route::middleware(['verified'])->group(function (): void {
                Route::put('user', [AuthController::class, 'updateUser']);
                Route::put('user/password', [AuthController::class, 'updateUserPassword']);
                Route::post('user/email/new/notification', [AuthController::class, 'sendEmailChangeVerificationNotification'])
                    ->middleware(['throttle:6,1']);
                Route::get('user/email/new/verify/{id}/{email}/{hash}', [AuthController::class, 'verifyNewEmail'])
                    ->middleware(['signed', 'throttle:6,1'])
                    ->name('auth.user.email.new.verify');
            });
        });

        Route::middleware(['verified'])->group(function (): void {
            Route::apiResource('collections', CollectionController::class);
            Route::apiResource('products/categories', ProductCategoryController::class);
            Route::apiResource('products/types', ProductTypeController::class);
            Route::put('products', [ProductController::class, 'bulkUpdate'])->name('products.bulk-update');
            Route::delete('products', [ProductController::class, 'bulkDestroy'])->name('products.bulk-destroy');
            Route::apiResource('products', ProductController::class);
            Route::apiResource('vendors', VendorController::class);
            Route::delete('customers', [CustomerController::class, 'bulkDestroy'])->name('customers.bulk-destroy');
            Route::apiResource('customers', CustomerController::class);
            Route::apiResource('customers.addresses', CustomerAddressController::class);
        });
    });
});
