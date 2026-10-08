<?php

namespace Euginepj\ImageResizer\Tests\Feature;

use Euginepj\ImageResizer\Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadEndpointTest extends TestCase
{
    private function payload(array $override = []): array
    {
        return array_merge([
            'image'       => UploadedFile::fake()->image('photo.png', 400, 300),
            'crop_x'      => 10,
            'crop_y'      => 10,
            'crop_width'  => 200,
            'crop_height' => 150,
            'crop_rotate' => 0,
            'formats'     => ['webp', 'jpg', 'png'],
        ], $override);
    }

    public function test_successful_crop_returns_all_formats(): void
    {
        Storage::fake('public');

        $res = $this->postJson(route('image-resizer.upload'), $this->payload());

        $res->assertOk()->assertJsonPath('status', 'success')->assertJsonStructure(['paths' => ['webp', 'jpg', 'png'], 'urls']);
        foreach ($res->json('paths') as $p) {
            Storage::disk('public')->assertExists($p);
        }
    }

    public function test_out_of_bounds_crop_is_clamped(): void
    {
        Storage::fake('public');

        $res = $this->postJson(route('image-resizer.upload'), $this->payload([
            'crop_x' => 350, 'crop_width' => 900, 'formats' => ['png'],
        ]));

        $res->assertOk();
        $size = getimagesizefromstring(Storage::disk('public')->get($res->json('paths.png')));
        $this->assertSame(50, $size[0]);
    }

    public function test_target_size_is_applied(): void
    {
        Storage::fake('public');

        $res = $this->postJson(route('image-resizer.upload'), $this->payload([
            'target_width' => 100, 'target_height' => 75, 'formats' => ['png'],
        ]));

        $size = getimagesizefromstring(Storage::disk('public')->get($res->json('paths.png')));
        $this->assertSame([100, 75], [$size[0], $size[1]]);
    }

    public function test_non_image_upload_is_rejected(): void
    {
        $res = $this->postJson(route('image-resizer.upload'), $this->payload([
            'image' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
        ]));

        $res->assertStatus(422)->assertJsonValidationErrors('image');
    }

    public function test_path_traversal_in_folder_is_rejected(): void
    {
        $res = $this->postJson(route('image-resizer.upload'), $this->payload(['folder' => '../../etc']));

        $res->assertStatus(422)->assertJsonValidationErrors('folder');
    }

    public function test_custom_folder_is_used(): void
    {
        Storage::fake('public');

        $res = $this->postJson(route('image-resizer.upload'), $this->payload(['folder' => 'avatars/2026', 'formats' => ['png']]));

        $this->assertStringStartsWith('avatars/2026/', $res->json('paths.png'));
    }

    public function test_missing_coordinates_are_rejected(): void
    {
        $res = $this->postJson(route('image-resizer.upload'), ['image' => UploadedFile::fake()->image('a.png')]);

        $res->assertStatus(422)->assertJsonValidationErrors(['crop_x', 'crop_y', 'crop_width', 'crop_height']);
    }

    public function test_endpoint_is_throttled(): void
    {
        Storage::fake('public');

        $last = null;
        for ($i = 0; $i < 31; $i++) {
            $last = $this->postJson(route('image-resizer.upload'), $this->payload(['formats' => ['png']]));
        }

        $last->assertStatus(429);
    }
}