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
            'image'         => ['required', 'file', 'mimes:jpeg,jpg,png,webp,avif', 'max:20480'], // 20MB
            'crop_x'        => ['required', 'numeric'],
            'crop_y'        => ['required', 'numeric'],
            'crop_width'    => ['required', 'numeric', 'min:1'],
            'crop_height'   => ['required', 'numeric', 'min:1'],
            'crop_rotate'   => ['nullable', 'numeric'],
            'target_width'  => ['nullable', 'numeric', 'min:1'],
            'target_height' => ['nullable', 'numeric', 'min:1'],
            'formats'       => ['nullable', 'array'],
            'formats.*'     => ['string', 'in:webp,jpg,jpeg,png,avif'],
            'folder'        => ['nullable', 'string'],
        ];
    }
}
