<?php

namespace Euginepj\ImageResizer\Tests\Feature;

use Euginepj\ImageResizer\Tests\TestCase;
use Euginepj\ImageResizer\Facades\ImageResizer;
use Euginepj\ImageResizer\Enums\ImageFormat;
use Illuminate\Support\Facades\Storage;

class ImageResizerTest extends TestCase
{
    public function test_facade_can_instantiate_service(): void
    {
        Storage::fake('public');

        $this->assertNotNull(ImageResizer::getFacadeRoot());
    }

    public function test_enum_parsing(): void
    {
        $this->assertEquals(ImageFormat::WEBP, ImageFormat::fromString('webp'));
        $this->assertEquals(ImageFormat::JPEG, ImageFormat::fromString('jpeg'));
        $this->assertEquals(ImageFormat::JPEG, ImageFormat::fromString('jpg'));
        $this->assertEquals(ImageFormat::PNG, ImageFormat::fromString('png'));
    }
}
