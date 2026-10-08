# Changelog

## v0.1.1
- **Studio Canvas & Teleport Modal**: `<template x-teleport="body">` rendering at `z-[99999]` preventing parent layout clipping with dark neutral studio canvas.
- **Enhanced Transform Controls**: Fluid zoom slider (0.1x to 3.0x), 90° rotation, horizontal & vertical flips, and one-click transform reset.
- **Modular View Architecture**: Split `cropper.blade.php` into reusable sub-views (`modal.blade.php`, `controls.blade.php`, `scripts.blade.php`) and added custom trigger slot support.
- **Stability & Bug Fixes**: Fixed consecutive file re-selection issue (`e.target.value = ''`), added safe image decode loading, and added Blade component test coverage.

## v0.1.0
- Interactive `<x-image-resizer::cropper />` Blade component (Alpine.js + Cropper.js).
- Server-side crop, rotate, resize with Intervention Image v3 (GD / Imagick).
- Multi-format export: WebP, JPEG, PNG, AVIF.
- Secured upload endpoint: throttling, configurable middleware, size/pixel limits, folder sanitising, crop clamping.
- Transparent rotation fill for PNG/WebP.
- Configurable asset URLs, `ImageResizer::delete()`, test suite.