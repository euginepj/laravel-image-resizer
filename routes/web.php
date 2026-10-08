<?php

use Illuminate\Support\Facades\Route;
use Euginepj\ImageResizer\Http\Controllers\ImageCropController;

Route::prefix(config('image-resizer.routes.prefix', 'image-resizer'))
    ->middleware(config('image-resizer.routes.middleware', ['web']))
    ->group(function () {
        Route::post('/upload', ImageCropController::class)->name('image-resizer.upload');
    });