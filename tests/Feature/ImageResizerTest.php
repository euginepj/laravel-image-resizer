<?php

namespace Euginepj\ImageResizer\Tests\Feature;

use Euginepj\ImageResizer\Enums\ImageFormat;
use Euginepj\ImageResizer\Facades\ImageResizer;
use Euginepj\ImageResizer\Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class ImageResizerTest extends TestCase
{
    public function test_enum_parsing(): void
    {
        $this->assertSame(ImageFormat::WEBP, ImageFormat::fromString('webp'));
        $this->assertSame(ImageFormat::JPEG, ImageFormat::fromString('jpeg'));
        $this->assertSame(ImageFormat::JPEG, ImageFormat::fromString('JPG'));
        $this->assertSame(ImageFormat::PNG, ImageFormat::fromString('png'));
    }

    public function test_crop_produces_expected_dimensions(): void
    {
        Storage::fake('public');

        $path = ImageResizer::load($this->makeImage(200, 100))
            ->crop(50, 40, 10, 10)
            ->save('out/crop', ImageFormat::PNG);

        $this->assertSame('out/crop.png', $path);
        $size = getimagesizefromstring(Storage::disk('public')->get($path));
        $this->assertSame([50, 40], [$size[0], $size[1]]);
    }

    public function test_multi_format_creates_every_file(): void
    {
        Storage::fake('public');

        $paths = ImageResizer::load($this->makeImage())
            ->saveMultiFormat('out/multi', ['webp', 'jpg', 'png']);

        $this->assertSame(['webp', 'jpg', 'png'], array_keys($paths));
        foreach ($paths as $p) {
            Storage::disk('public')->assertExists($p);
        }
        $this->assertSame('image/webp', getimagesizefromstring(Storage::disk('public')->get($paths['webp']))['mime']);
        $this->assertSame('image/jpeg', getimagesizefromstring(Storage::disk('public')->get($paths['jpg']))['mime']);
    }

    public function test_directory_with_dots_is_not_mangled(): void
    {
        Storage::fake('public');

        $path = ImageResizer::load($this->makeImage())->save('v1.2/photo', 'png');

        $this->assertSame('v1.2/photo.png', $path);
    }

    public function test_rotation_keeps_transparent_corners_in_png(): void
    {
        Storage::fake('public');

        $path = ImageResizer::load($this->makeImage(100, 100))
            ->rotate(30)
            ->save('out/rot', 'png');

        $im = imagecreatefromstring(Storage::disk('public')->get($path));
        $alpha = (imagecolorat($im, 0, 0) >> 24) & 0x7F;
        $this->assertSame(127, $alpha, 'Corner should be fully transparent');
    }

    public function test_resize_scales_proportionally(): void
    {
        Storage::fake('public');

        $path = ImageResizer::load($this->makeImage(200, 100))->resize(100)->save('out/r', 'png');

        $size = getimagesizefromstring(Storage::disk('public')->get($path));
        $this->assertSame([100, 50], [$size[0], $size[1]]);
    }

    public function test_delete_removes_files(): void
    {
        Storage::fake('public');

        $service = ImageResizer::load($this->makeImage());
        $paths = $service->saveMultiFormat('out/del', ['png', 'jpg']);
        $service->delete($paths);

        foreach ($paths as $p) {
            Storage::disk('public')->assertMissing($p);
        }
    }

    public function test_oversized_image_is_rejected(): void
    {
        config(['image-resizer.max_pixels' => 100]);
        $this->expectException(InvalidArgumentException::class);

        ImageResizer::load($this->makeImage(200, 100));
    }

    public function test_non_image_is_rejected(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'txt');
        file_put_contents($file, 'not an image');
        $this->expectException(InvalidArgumentException::class);

        ImageResizer::load($file);
    }
}