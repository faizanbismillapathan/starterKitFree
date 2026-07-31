<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Theme Modes
    |--------------------------------------------------------------------------
    |
    | Light, dark and system themes are mandatory (05_Design_System.md §10).
    |
    */

    'modes' => ['light', 'dark', 'system'],

    'default_mode' => env('THEME_DEFAULT_MODE', 'system'),

    'cookie' => 'starter_kit_theme',

    'cookie_lifetime' => 60 * 24 * 365, // one year, in minutes

    /*
    |--------------------------------------------------------------------------
    | Design Tokens
    |--------------------------------------------------------------------------
    |
    | Documented spacing, radius and typography scales. These mirror the CSS
    | custom properties defined in resources/css/app.css and exist here so
    | server-side code (PDF, mail) can reference the same values.
    |
    */

    'spacing_scale' => [4, 8, 12, 16, 20, 24, 32, 40, 48, 64],

    'radius' => [
        'small' => '6px',
        'medium' => '10px',
        'large' => '14px',
        'x_large' => '18px',
    ],

    'font' => [
        'family' => 'Inter',
        'weights' => [400, 500, 600, 700, 800],
        'sizes' => [12, 14, 16, 18, 20, 24, 30, 36, 48],
    ],

    'animation' => [
        'min_duration' => 150,
        'max_duration' => 300,
    ],

];
