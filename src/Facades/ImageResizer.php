<?php

declare(strict_types=1);

namespace Euginepj\ImageResizer\Facades;

use Illuminate\Support\Facades\Facade;
use Euginepj\ImageResizer\Enums\ImageFormat;

/**
 * @method static \Euginepj\ImageResizer\Services\ImageResizerService load(mixed $source)
 * @method static \Euginepj\ImageResizer\Services\ImageResizerService rotate(float|int $angle, string $backgroundColor = '#ffffff')
 * @method static \Euginepj\ImageResizer\Services\ImageResizerService crop(int $width, int $height, int $offsetX = 0, int $offsetY = 0, string $position = 'top-left')
 * @method static \Euginepj\ImageResizer\Services\ImageResizerService resize(int $width, ?int $height = null)
 * @method static \Euginepj\ImageResizer\Services\ImageResizerService cover(int $width, int $height, string $position = 'center')
 * @method static \Euginepj\ImageResizer\Services\ImageResizerService disk(string $disk)
 * @method static string save(string $path, ImageFormat|string $format = ImageFormat::WEBP, ?int $quality = null)
 * @method static array saveMultiFormat(string $basePath, array $formats = [])
 * @method static array getUrls(array $savedPaths)
 * @method static bool delete(array|string $paths)
 * @method static int getWidth()
 * @method static int getHeight()
 *
 * @see \Euginepj\ImageResizer\Services\ImageResizerService
 */
class ImageResizer extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'image-resizer';
    }
}
