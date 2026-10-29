<?php

namespace Euginepj\ImageResizer\Tests\Feature;

use Euginepj\ImageResizer\Tests\TestCase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;

class RoutesDisabledTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('image-resizer.routes.enabled', false);
    }

    #[Test]
    public function test_no_routes_are_registered_when_disabled(): void
    {
        $this->assertFalse(Route::has('image-resizer.upload'));
    }
}