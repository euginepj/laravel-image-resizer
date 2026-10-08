<?php

declare(strict_types=1);

namespace Euginepj\ImageResizer\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Euginepj\ImageResizer\Enums\ImageFormat;
use Euginepj\ImageResizer\Facades\ImageResizer;
use Euginepj\ImageResizer\Http\Requests\CropImageRequest;
use InvalidArgumentException;
use Throwable;

class ImageCropController extends Controller
{
    public function __invoke(CropImageRequest $request): JsonResponse
    {
        try {
            $folder   = trim((string) ($request->input('folder') ?: config('image-resizer.upload_path', 'uploads/images')), '/');
            $basePath = $folder . '/' . bin2hex(random_bytes(10));

            $service = ImageResizer::load($request->file('image'));

            $rotate = (float) $request->input('crop_rotate', 0);
            if ($rotate != 0.0) {
                // Cropper.js rotates clockwise, Intervention rotates counter-clockwise.
                $service->rotate(-$rotate);
            }

            // Clamp the crop box to the (rotated) image bounds.
            $imgW = $service->getWidth();
            $imgH = $service->getHeight();

            $x = max(0, min((int) round((float) $request->input('crop_x')), $imgW - 1));
            $y = max(0, min((int) round((float) $request->input('crop_y')), $imgH - 1));
            $w = max(1, min((int) round((float) $request->input('crop_width')), $imgW - $x));
            $h = max(1, min((int) round((float) $request->input('crop_height')), $imgH - $y));

            $service->crop($w, $h, $x, $y);

            if ($request->filled('target_width')) {
                $service->resize((int) $request->input('target_width'), $request->filled('target_height') ? (int) $request->input('target_height') : null);
            }

            $formats = array_map(
                fn ($f) => ImageFormat::fromString((string) $f),
                $request->input('formats', config('image-resizer.default_formats', ['webp', 'jpg', 'png']))
            );

            $paths = $service->saveMultiFormat($basePath, $formats);

            return response()->json([
                'status' => 'success',
                'paths'  => $paths,
                'urls'   => $service->getUrls($paths),
                'base'   => $basePath,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            Log::error('image-resizer: processing failed', ['exception' => $e]);

            return response()->json(['status' => 'error', 'message' => 'The image could not be processed.'], 500);
        }
    }
}