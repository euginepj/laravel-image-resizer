<?php

declare(strict_types=1);

namespace Euginepj\ImageResizer;

use Illuminate\Support\ServiceProvider;
use Euginepj\ImageResizer\Services\ImageResizerService;

class ImageResizerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/image-resizer.php', 'image-resizer');

        $this->app->bind('image-resizer', function ($app) {
            return new ImageResizerService($app['config']->get('image-resizer', []));
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'image-resizer');

        if (config('image-resizer.routes.enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/image-resizer.php' => config_path('image-resizer.php'),
            ], 'image-resizer-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/image-resizer'),
            ], 'image-resizer-views');
        }
    }
}