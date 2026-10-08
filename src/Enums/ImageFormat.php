<?php

declare(strict_types=1);

namespace Euginepj\ImageResizer\Enums;

enum ImageFormat: string
{
    case WEBP = 'webp';
    case JPEG = 'jpg';
    case PNG  = 'png';
    case AVIF = 'avif';

    public static function fromString(string $format): self
    {
        $normalized = strtolower(trim($format));
        if ($normalized === 'jpeg') {
            return self::JPEG;
        }

        return self::from($normalized);
    }
}
