<?php

declare(strict_types=1);

namespace Euginepj\ImageResizer\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Interfaces\ImageInterface;
use Euginepj\ImageResizer\Enums\ImageFormat;
use InvalidArgumentException;

class ImageResizerService
{
    protected ImageManager $manager;
    protected ?ImageInterface $image = null;
    protected string $disk;
    protected array $qualityConfig;

    public function __construct(array $config = [])
    {
        $driverClass = ($config['driver'] ?? 'gd') === 'imagick'
            ? ImagickDriver::class
            : GdDriver::class;

        $this->manager = new ImageManager(new $driverClass());
        $this->disk = $config['disk'] ?? 'public';
        $this->qualityConfig = $config['quality'] ?? [
            'webp' => 82,
            'jpg'  => 85,
            'png'  => 90,
            'avif' => 75,
        ];
    }

    /**
     * Load an image from an UploadedFile, file path, URL, or binary stream.
     */
    public function load(mixed $source): self
    {
        if ($source instanceof UploadedFile) {
            $this->image = $this->manager->read($source->getRealPath());
        } elseif (is_string($source)) {
            $this->image = $this->manager->read($source);
        } else {
            throw new InvalidArgumentException('Unsupported image source type.');
        }

        return $this;
    }

    /**
     * Get the current ImageInterface instance.
     */
    public function getImage(): ImageInterface
    {
        if (!$this->image) {
            throw new InvalidArgumentException('No image loaded. Call load() first.');
        }
        return $this->image;
    }

    /**
     * Rotate the image by given angle in degrees.
     */
    public function rotate(float|int $angle, string $backgroundColor = '#ffffff'): self
    {
        $this->getImage()->rotate((float)$angle, $backgroundColor);
        return $this;
    }

    /**
     * Crop a specific rectangular region.
     */
    public function crop(int $width, int $height, int $offsetX = 0, int $offsetY = 0, string $position = 'top-left'): self
    {
        $this->getImage()->crop($width, $height, $offsetX, $offsetY, $position);
        return $this;
    }

    /**
     * Scale image proportionally to fit within max width and height.
     */
    public function resize(int $width, ?int $height = null): self
    {
        $this->getImage()->scale(width: $width, height: $height);
        return $this;
    }

    /**
     * Crop and resize image to exact dimensions (Fit / Cover).
     */
    public function cover(int $width, int $height, string $position = 'center'): self
    {
        $this->getImage()->cover($width, $height, $position);
        return $this;
    }

    /**
     * Set target storage disk.
     */
    public function disk(string $disk): self
    {
        $this->disk = $disk;
        return $this;
    }

    /**
     * Save the current image into a single target format.
     */
    public function save(string $path, ImageFormat|string $format = ImageFormat::WEBP, ?int $quality = null): string
    {
        $formatEnum = is_string($format) ? ImageFormat::fromString($format) : $format;
        $quality = $quality ?? ($this->qualityConfig[$formatEnum->value] ?? 85);

        $encoded = match ($formatEnum) {
            ImageFormat::WEBP => $this->getImage()->toWebp($quality),
            ImageFormat::JPEG => $this->getImage()->toJpeg($quality),
            ImageFormat::PNG  => $this->getImage()->toPng(),
            ImageFormat::AVIF => $this->getImage()->toAvif($quality),
        };

        $basePath = preg_replace('/\.[^.]+$/', '', $path);
        $finalPath = $basePath . '.' . $formatEnum->value;

        Storage::disk($this->disk)->put($finalPath, (string) $encoded);

        return $finalPath;
    }

    /**
     * Save the image into multiple formats simultaneously (e.g. WebP, JPG, PNG).
     *
     * @param array<ImageFormat|string> $formats
     * @return array<string, string> Map of format => relative storage path
     */
    public function saveMultiFormat(string $basePath, array $formats = [ImageFormat::WEBP, ImageFormat::JPEG, ImageFormat::PNG]): array
    {
        $results = [];

        foreach ($formats as $format) {
            $formatEnum = is_string($format) ? ImageFormat::fromString($format) : $format;
            $results[$formatEnum->value] = $this->save($basePath, $formatEnum);
        }

        return $results;
    }

    /**
     * Returns full URLs for saved format paths based on the configured disk.
     *
     * @param array<string, string> $savedPaths
     * @return array<string, string>
     */
    public function getUrls(array $savedPaths): array
    {
        $urls = [];
        $storage = Storage::disk($this->disk);

        foreach ($savedPaths as $format => $path) {
            $urls[$format] = $storage->url($path);
        }

        return $urls;
    }
}
