<?php

use Illuminate\Support\Facades\Route;

Route::prefix('s')->group(function () {
    require __DIR__ . '/auth.php';
});
