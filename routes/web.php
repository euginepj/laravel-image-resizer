<?php

use Illuminate\Support\Facades\Route;
use Euginepj\ImageResizer\Http\Controllers\ImageCropController;

$prefix = config('image-resizer.routes.prefix', 'image-resizer');
$middleware = config('image-resizer.routes.middleware', ['web']);

Route::prefix($prefix)
    ->middleware($middleware)
    ->group(function () {
        Route::post('/upload', ImageCropController::class)->name('image-resizer.upload');
    });
