<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

require __DIR__.'/../app/Routes/Cms/web.php';
require __DIR__.'/../app/Routes/Store/web.php';
