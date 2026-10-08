<?php

declare(strict_types=1);

namespace Euginepj\ImageResizer\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use Euginepj\ImageResizer\Enums\ImageFormat;
use InvalidArgumentException;

class ImageResizerService
{
    protected ImageManager $manager;
    protected ?ImageInterface $image = null;
    protected string $disk;
    protected array $qualityConfig;
    protected int $maxPixels;

    public function __construct(array $config = [])
    {
        $driverClass = ($config['driver'] ?? 'gd') === 'imagick'
            ? ImagickDriver::class
            : GdDriver::class;

        $this->manager = new ImageManager(new $driverClass());
        $this->disk = $config['disk'] ?? 'public';
        $this->maxPixels = (int) ($config['max_pixels'] ?? 40000000);
        $this->qualityConfig = $config['quality'] ?? [
            'webp' => 82,
            'jpg'  => 85,
            'png'  => 90,
            'avif' => 75,
        ];
    }

    /**
     * Load an image from an UploadedFile or a file path / binary string.
     */
    public function load(mixed $source): self
    {
        if ($source instanceof UploadedFile) {
            $source = $source->getRealPath();
        }

        if (!is_string($source) || $source === '') {
            throw new InvalidArgumentException('Unsupported image source type.');
        }

        // Check the pixel count before decoding to protect memory.
        if (@is_file($source)) {
            $info = @getimagesize($source);
            if ($info === false) {
                throw new InvalidArgumentException('The file is not a valid image.');
            }
            if ($info[0] * $info[1] > $this->maxPixels) {
                throw new InvalidArgumentException('The image dimensions are too large.');
            }
        }

        $this->image = $this->manager->read($source);

        return $this;
    }

    public function getImage(): ImageInterface
    {
        if (!$this->image) {
            throw new InvalidArgumentException('No image loaded. Call load() first.');
        }

        return $this->image;
    }

    public function getWidth(): int
    {
        return $this->getImage()->width();
    }

    public function getHeight(): int
    {
        return $this->getImage()->height();
    }

    /**
     * Rotate the image (counter-clockwise). The empty corners stay transparent
     * (JPEG output flattens them to white).
     */
    public function rotate(float|int $angle, string $background = 'rgba(255, 255, 255, 0)'): self
    {
        $this->getImage()->rotate((float) $angle, $background);

        return $this;
    }

    /**
     * Crop a rectangular region starting at the given offset.
     */
    public function crop(int $width, int $height, int $offsetX = 0, int $offsetY = 0, string $position = 'top-left'): self
    {
        $this->getImage()->crop($width, $height, $offsetX, $offsetY, position: $position);

        return $this;
    }

    /**
     * Scale proportionally to fit within the given size.
     */
    public function resize(int $width, ?int $height = null): self
    {
        $this->getImage()->scale(width: $width, height: $height);

        return $this;
    }

    /**
     * Crop and resize to exact dimensions.
     */
    public function cover(int $width, int $height, string $position = 'center'): self
    {
        $this->getImage()->cover($width, $height, $position);

        return $this;
    }

    public function disk(string $disk): self
    {
        $this->disk = $disk;

        return $this;
    }

    /**
     * Encode and store the image in one format. Returns the stored path.
     */
    public function save(string $path, ImageFormat|string $format = ImageFormat::WEBP, ?int $quality = null): string
    {
        $format  = is_string($format) ? ImageFormat::fromString($format) : $format;
        $quality = $quality ?? ($this->qualityConfig[$format->value] ?? 85);
        $image   = $this->getImage();

        $encoded = match ($format) {
            ImageFormat::WEBP => $image->toWebp($quality),
            ImageFormat::JPEG => $image->toJpeg($quality),
            ImageFormat::PNG  => $image->toPng(),
            ImageFormat::AVIF => $image->toAvif($quality),
        };

        $dir  = pathinfo($path, PATHINFO_DIRNAME);
        $name = pathinfo($path, PATHINFO_FILENAME);
        $finalPath = (($dir !== '' && $dir !== '.') ? $dir . '/' : '') . $name . '.' . $format->value;

        Storage::disk($this->disk)->put($finalPath, (string) $encoded);

        return $finalPath;
    }

    /**
     * Store the image in several formats at once.
     *
     * @param array<ImageFormat|string> $formats
     * @return array<string, string> format => stored path
     */
    public function saveMultiFormat(string $basePath, array $formats = [ImageFormat::WEBP, ImageFormat::JPEG, ImageFormat::PNG]): array
    {
        $results = [];

        foreach ($formats as $format) {
            $format = is_string($format) ? ImageFormat::fromString($format) : $format;
            $results[$format->value] = $this->save($basePath, $format);
        }

        return $results;
    }

    /**
     * Public URLs for stored paths.
     *
     * @param array<string, string> $savedPaths
     * @return array<string, string>
     */
    public function getUrls(array $savedPaths): array
    {
        $storage = Storage::disk($this->disk);

        return array_map(fn (string $path) => $storage->url($path), $savedPaths);
    }

    /**
     * Delete previously stored files (e.g. the array returned by saveMultiFormat()).
     *
     * @param array<string>|string $paths
     */
    public function delete(array|string $paths): bool
    {
        return Storage::disk($this->disk)->delete(array_values((array) $paths));
    }
}