<?php

declare(strict_types=1);

namespace Euginepj\ImageResizer\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Euginepj\ImageResizer\Facades\ImageResizer;
use Euginepj\ImageResizer\Http\Requests\CropImageRequest;
use Euginepj\ImageResizer\Enums\ImageFormat;
use Throwable;

class ImageCropController extends Controller
{
    public function __invoke(CropImageRequest $request): JsonResponse
    {
        try {
            $file = $request->file('image');
            $folder = trim($request->input('folder', config('image-resizer.upload_path', 'uploads/images')), '/');
            $filename = uniqid('img_', true);
            $basePath = "{$folder}/{$filename}";

            // 1. Load image
            $service = ImageResizer::load($file);

            // 2. Rotate if requested
            $rotate = (float) $request->input('crop_rotate', 0);
            if ($rotate != 0) {
                // Invert rotation angle because canvas/cropperjs rotate clockwise
                $service->rotate(-$rotate);
            }

            // 3. Crop coordinates
            $cropX = (int) round((float) $request->input('crop_x', 0));
            $cropY = (int) round((float) $request->input('crop_y', 0));
            $cropWidth = (int) round((float) $request->input('crop_width'));
            $cropHeight = (int) round((float) $request->input('crop_height'));

            $service->crop($cropWidth, $cropHeight, $cropX, $cropY);

            // 4. Scale to target output size if given
            $targetWidth = $request->filled('target_width') ? (int) $request->input('target_width') : null;
            $targetHeight = $request->filled('target_height') ? (int) $request->input('target_height') : null;

            if ($targetWidth && $targetWidth > 0) {
                $service->resize($targetWidth, $targetHeight);
            }

            // 5. Multi-format export
            $requestedFormats = $request->input('formats', config('image-resizer.default_formats', ['webp', 'jpg', 'png']));
            $formatEnums = array_map(fn($f) => ImageFormat::fromString((string)$f), $requestedFormats);

            $paths = $service->saveMultiFormat($basePath, $formatEnums);
            $urls = $service->getUrls($paths);

            return response()->json([
                'status'  => 'success',
                'paths'   => $paths,
                'urls'    => $urls,
                'base'    => $basePath,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
