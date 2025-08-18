<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('s')->group(function (): void {
    require __DIR__.'/auth.php';
});
