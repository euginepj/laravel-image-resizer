<?php

declare(strict_types=1);

namespace Euginepj\ImageResizer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CropImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image'         => ['required', 'file', 'mimes:jpeg,jpg,png,webp,gif', 'max:' . (int) config('image-resizer.max_upload_kb', 20480)],
            'crop_x'        => ['required', 'numeric'],
            'crop_y'        => ['required', 'numeric'],
            'crop_width'    => ['required', 'numeric', 'min:1'],
            'crop_height'   => ['required', 'numeric', 'min:1'],
            'crop_rotate'   => ['nullable', 'numeric', 'between:-360,360'],
            'target_width'  => ['nullable', 'integer', 'min:1', 'max:8000'],
            'target_height' => ['nullable', 'integer', 'min:1', 'max:8000'],
            'formats'       => ['nullable', 'array', 'max:4'],
            'formats.*'     => ['string', 'in:webp,jpg,jpeg,png,avif'],
            // letters, digits, dash, underscore and slash only (no dots => no "..")
            'folder'        => ['nullable', 'string', 'max:150', 'regex:/^[A-Za-z0-9_\-\/]+$/'],
        ];
    }
}