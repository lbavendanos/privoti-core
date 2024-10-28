<?php

use Illuminate\Support\Facades\Route;

Route::prefix('d')->group(function () {
    require __DIR__ . '/../app/Domains/Cms/Routes/auth.php';
});

Route::prefix('s')->group(function () {
    require __DIR__ . '/../app/Domains/Store/Routes/auth.php';
});
