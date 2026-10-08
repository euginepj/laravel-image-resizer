# Laravel Image Resizer & Multi-Format Converter

[![Latest Version on Packagist](https://img.shields.io/packagist/v/euginepj/laravel-image-resizer.svg?style=flat-square)](https://packagist.org/packages/euginepj/laravel-image-resizer)
[![Total Downloads](https://img.shields.io/packagist/dt/euginepj/laravel-image-resizer.svg?style=flat-square)](https://packagist.org/packages/euginepj/laravel-image-resizer)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)

A modern, high-performance Laravel package for interactive image cropping, resizing, and multi-format conversion (**WebP, JPEG, PNG, AVIF**) powered by **Intervention Image v3** and **Alpine.js + Cropper.js**.

---

## Features

- ðŸŽ¯ **Interactive Visual Cropper**: Ready-to-use `<x-image-resizer::cropper />` Blade component with drag, zoom, and rotate controls.
- âš¡ **Multi-Format Export**: Automatically generates and stores WebP, JPEG, and PNG formats simultaneously from a single crop.
- ðŸ’Ž **Lossless Coordinate Processing**: Client sends integer crop coordinates; the server renders crisp, high-resolution output.
- ðŸš€ **Intervention Image v3**: Supports both GD and Imagick drivers.
- ðŸ“¦ **Storage Flexibility**: Seamlessly works with local, public, or AWS S3 disks.
- ðŸ› ï¸ **Fluent Backend API**: Chainable methods for programmatic usage inside your controllers or queue jobs.

---

## Installation

You can install the package via Composer:

```bash
composer require euginepj/laravel-image-resizer
```

Publish the config and views (optional):

```bash
php artisan vendor:publish --tag="image-resizer-config"
php artisan vendor:publish --tag="image-resizer-views"
```

---

## Quick Start (Frontend Blade Component)

Just place the component in your Blade view:

```blade
<!-- In your Blade view: -->
<x-image-resizer::cropper 
    name="avatar" 
    :aspect-ratio="1" 
    :target-width="400" 
    :target-height="400" 
    :formats="['webp', 'jpg', 'png']"
/>

<!-- Catch the output paths/urls in your form -->
<div x-data="{ avatarWebp: '', avatarJpg: '' }" 
     @image-cropped.window="if ($event.detail.name === 'avatar') { avatarWebp = $event.detail.paths.webp; avatarJpg = $event.detail.paths.jpg; }">
    
    <input type="hidden" name="avatar_webp" :value="avatarWebp">
    <input type="hidden" name="avatar_jpg" :value="avatarJpg">
</div>
```

---

## Programmatic Usage (Backend)

You can also use the `ImageResizer` facade anywhere in your Laravel application:

```php
use Euginepj\ImageResizer\Facades\ImageResizer;
use Euginepj\ImageResizer\Enums\ImageFormat;

// 1. Precise crop & multi-format export
$paths = ImageResizer::load($request->file('avatar'))
    ->rotate(90)
    ->crop(width: 500, height: 500, offsetX: 100, offsetY: 50)
    ->resize(width: 300, height: 300)
    ->saveMultiFormat('uploads/avatars/user-123', [
        ImageFormat::WEBP,
        ImageFormat::JPEG,
        ImageFormat::PNG,
    ]);

/*
$paths returns:
[
    'webp' => 'uploads/avatars/user-123.webp',
    'jpg'  => 'uploads/avatars/user-123.jpg',
    'png'  => 'uploads/avatars/user-123.png',
]
*/
```

---

## Testing

```bash
composer test
```

## Security

If you discover any security issues, please submit an issue or pull request directly to the GitHub repository.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
