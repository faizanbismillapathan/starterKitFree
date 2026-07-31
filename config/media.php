<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    |
    | The media system is storage independent (16_Media_System.md §5). Swapping
    | the disk requires no application code changes.
    |
    */

    'disk' => env('MEDIA_DISK', 'public'),

    'directory' => env('MEDIA_DIRECTORY', 'media'),

    /*
    |--------------------------------------------------------------------------
    | Upload Validation
    |--------------------------------------------------------------------------
    |
    | Validation is always enforced on the server (16_Media_System.md §9).
    |
    */

    'max_upload_size' => (int) env('MEDIA_MAX_UPLOAD_SIZE', 5120), // kilobytes

    'allowed_mime_types' => [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'application/pdf',
    ],

    'allowed_extensions' => [
        'jpg',
        'jpeg',
        'png',
        'webp',
        'gif',
        'pdf',
    ],

    'image_mime_types' => [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Processing
    |--------------------------------------------------------------------------
    |
    | Original files are always preserved (16_Media_System.md §12).
    |
    */

    'conversions' => [
        'small' => ['width' => 64, 'height' => 64],
        'medium' => ['width' => 256, 'height' => 256],
        'large' => ['width' => 1024, 'height' => 1024],
    ],

    'optimize' => [
        'quality' => (int) env('MEDIA_IMAGE_QUALITY', 82),
        'max_width' => (int) env('MEDIA_IMAGE_MAX_WIDTH', 2400),
    ],

    /*
    |--------------------------------------------------------------------------
    | Avatars
    |--------------------------------------------------------------------------
    */

    'avatar' => [
        'collection' => 'avatar',
        'max_size' => (int) env('MEDIA_AVATAR_MAX_SIZE', 2048), // kilobytes
        'mime_types' => ['image/jpeg', 'image/png', 'image/webp'],
    ],

];
