<?php

use Illuminate\Support\Facades\Route;

Route::prefix('d')->group(function () {
    require __DIR__ . '/../app/Domains/D/Routes/auth.php';
});

Route::prefix('w')->group(function () {
    require __DIR__ . '/../app/Domains/W/Routes/auth.php';
});
