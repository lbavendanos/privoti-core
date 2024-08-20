<?php

use Illuminate\Support\Facades\Route;

Route::prefix('d')->group(function () {
    require __DIR__ . '/d/auth.php';
});

Route::prefix('w')->group(function () {
    require __DIR__ . '/w/auth.php';
});
