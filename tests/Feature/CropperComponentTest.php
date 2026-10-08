<?php

namespace Euginepj\ImageResizer\Tests\Feature;

use Euginepj\ImageResizer\Tests\TestCase;
use Illuminate\Support\Facades\Blade;

class CropperComponentTest extends TestCase
{
    public function test_cropper_component_renders_successfully(): void
    {
        $rendered = Blade::render(
            '<x-image-resizer::cropper name="avatar" :aspect-ratio="1" :target-width="300" :target-height="300" button-label="Upload Avatar" />'
        );

        $this->assertStringContainsString('Upload Avatar', $rendered);
        $this->assertStringContainsString('imageCropperComponent', $rendered);
        $this->assertStringContainsString('x-teleport="body"', $rendered);
        $this->assertStringContainsString('targetWidth: 300', $rendered);
        $this->assertStringContainsString('targetHeight: 300', $rendered);
        $this->assertStringContainsString('toggleFlipX()', $rendered);
        $this->assertStringContainsString('toggleFlipY()', $rendered);
    }

    public function test_cropper_component_renders_with_custom_trigger(): void
    {
        $rendered = Blade::render(
            '<x-image-resizer::cropper name="photo">
                <x-slot:trigger>
                    <button type="button" id="custom-btn">Custom Trigger</button>
                </x-slot:trigger>
            </x-image-resizer::cropper>'
        );

        $this->assertStringContainsString('Custom Trigger', $rendered);
        $this->assertStringContainsString('id="custom-btn"', $rendered);
    }
}
