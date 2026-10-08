<?php

namespace Euginepj\ImageResizer\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Euginepj\ImageResizer\ImageResizerServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            ImageResizerServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('image-resizer.disk', 'public');
    }
}
