# Laravel Image Resizer

Interactive image cropping, resizing and multi-format conversion (**WebP, JPEG, PNG, AVIF**) for Laravel, powered by **Intervention Image v3** and **Alpine.js + Cropper.js**.

The browser sends crop coordinates; the server crops the original file, so quality is never degraded by a canvas re-encode.

## Requirements

- PHP 8.2+ with the **GD** (with WebP/AVIF support as needed) or **Imagick** extension
- Laravel 10, 11 or 12
- **Alpine.js v3** loaded on the page (the component does not bundle it)
- `php artisan storage:link` when using the `public` disk

## Installation

```bash
composer require euginepj/laravel-image-resizer
php artisan vendor:publish --tag=image-resizer-config   # optional
php artisan vendor:publish --tag=image-resizer-views    # optional
```

## Blade component

```blade
<x-image-resizer::cropper
    name="avatar"
    :aspect-ratio="1"
    :target-width="400"
    :target-height="400"
    :formats="['webp', 'jpg', 'png']"
    folder="avatars"
/>

<div x-data="{ urls: null }" @image-cropped.window="urls = $event.detail.urls">
    <template x-if="urls">
        <picture>
            <source :srcset="urls.webp" type="image/webp">
            <img :src="urls.jpg" alt="Avatar">
        </picture>
    </template>
</div>
```

The `image-cropped` event detail contains `name`, `paths`, `urls` and `base`.
Component props: `name`, `aspect-ratio`, `target-width`, `target-height`, `formats`, `folder`, `upload-url`, `button-label`, `modal-title`.

## Programmatic usage

```php
use Euginepj\ImageResizer\Facades\ImageResizer;

$paths = ImageResizer::load($request->file('photo'))
    ->rotate(90)
    ->crop(width: 500, height: 500, offsetX: 100, offsetY: 50)
    ->resize(300, 300)
    ->saveMultiFormat('uploads/avatars/user-123', ['webp', 'jpg', 'png']);

ImageResizer::delete($paths);
```

## Security

The built-in endpoint `POST /image-resizer/upload` is throttled (`30/min`) but **not authenticated by default**. Restrict it in `config/image-resizer.php`:

```php
'routes' => ['enabled' => true, 'prefix' => 'image-resizer', 'middleware' => ['web', 'auth', 'throttle:30,1']],
```

Set `'enabled' => false` to disable the route entirely and use the facade from your own controller.
Uploads are limited by `max_upload_kb` and `max_pixels`; `folder` accepts only letters, digits, `-`, `_` and `/`.

## Configuration

See [`config/image-resizer.php`](config/image-resizer.php): driver, quality per format, default formats, disk, upload path, limits, routes and asset URLs (use your own files instead of the CDN).

## Testing

```bash
composer install
composer test
```

## License

MIT