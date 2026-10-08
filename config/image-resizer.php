<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Image Processing Driver
    |--------------------------------------------------------------------------
    | Supported: "gd" or "imagick"
    | Note: Intervention Image v3 supports both drivers.
    */
    'driver' => env('IMAGE_RESIZER_DRIVER', 'gd'),

    /*
    |--------------------------------------------------------------------------
    | Default Output Quality (1 - 100)
    |--------------------------------------------------------------------------
    */
    'quality' => [
        'webp' => 82,
        'jpg'  => 85,
        'jpeg' => 85,
        'png'  => 90,
        'avif' => 75,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Target Formats for Multi-Format Generation
    |--------------------------------------------------------------------------
    */
    'default_formats' => ['webp', 'jpg', 'png'],

    /*
    |--------------------------------------------------------------------------
    | Storage Disk
    |--------------------------------------------------------------------------
    | Target disk configured in config/filesystems.php (e.g. 'public', 's3')
    */
    'disk' => env('IMAGE_RESIZER_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Upload Directory
    |--------------------------------------------------------------------------
    | Relative path inside the disk where processed images are stored.
    */
    'upload_path' => 'uploads/images',

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    | Route prefix and middleware for package crop endpoint.
    */
    'routes' => [
        'prefix' => 'image-resizer',
        'middleware' => ['web'],
    ],
];
