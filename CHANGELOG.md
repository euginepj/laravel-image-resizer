# Changelog

## v0.1.0
- Interactive `<x-image-resizer::cropper />` Blade component (Alpine.js + Cropper.js).
- Server-side crop, rotate, resize with Intervention Image v3 (GD / Imagick).
- Multi-format export: WebP, JPEG, PNG, AVIF.
- Secured upload endpoint: throttling, configurable middleware, size/pixel limits, folder sanitising, crop clamping.
- Transparent rotation fill for PNG/WebP.
- Configurable asset URLs, `ImageResizer::delete()`, test suite.