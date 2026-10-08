<?php

namespace Euginepj\ImageResizer\Tests;

use Euginepj\ImageResizer\ImageResizerServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [ImageResizerServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('image-resizer.disk', 'public');
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
    }

    /** Create a solid red PNG on disk and return its path. */
    protected function makeImage(int $w = 200, int $h = 100): string
    {
        $path = tempnam(sys_get_temp_dir(), 'imgr') . '.png';
        $im = imagecreatetruecolor($w, $h);
        imagefill($im, 0, 0, imagecolorallocate($im, 255, 0, 0));
        imagepng($im, $path);
        imagedestroy($im);

        return $path;
    }
}