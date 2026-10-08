<?php

namespace Euginepj\ImageResizer\Tests\Unit;

use Euginepj\ImageResizer\Enums\ImageFormat;
use Euginepj\ImageResizer\Services\ImageResizerService;
use Euginepj\ImageResizer\Tests\TestCase;
use InvalidArgumentException;

class ImageResizerServiceUnitTest extends TestCase
{
    private ImageResizerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ImageResizerService([
            'driver' => 'gd',
            'disk'   => 'public',
            'quality' => [
                'webp' => 80,
                'jpg'  => 85,
                'png'  => 90,
            ],
            'max_pixels' => 1000000,
        ]);
    }

    public function test_throws_exception_when_no_image_loaded(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No image loaded. Call load() first.');

        $this->service->getImage();
    }

    public function test_throws_exception_for_empty_source(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported image source type.');

        $this->service->load('');
    }

    public function test_can_load_valid_image_and_read_dimensions(): void
    {
        $path = $this->makeImage(150, 80);
        $this->service->load($path);

        $this->assertSame(150, $this->service->getWidth());
        $this->assertSame(80, $this->service->getHeight());
    }

    public function test_image_format_enum_helper(): void
    {
        $this->assertSame(ImageFormat::WEBP, ImageFormat::fromString('WEBP'));
        $this->assertSame(ImageFormat::JPEG, ImageFormat::fromString('jpeg'));
        $this->assertSame(ImageFormat::JPEG, ImageFormat::fromString('jpg'));
        $this->assertSame(ImageFormat::PNG, ImageFormat::fromString('PNG'));
        $this->assertSame(ImageFormat::AVIF, ImageFormat::fromString('avif'));
    }
}