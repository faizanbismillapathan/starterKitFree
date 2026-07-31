<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Product Identity
    |--------------------------------------------------------------------------
    |
    | Branding values consumed by layouts, emails and PDF documents. These may
    | be overridden per deployment without touching application source code.
    |
    */

    'name' => env('STARTER_KIT_NAME', 'Laravel Business Starter Kit'),

    'edition' => env('STARTER_KIT_EDITION', 'Community'),

    'version' => '1.0.0',

    'company' => [
        'name' => env('STARTER_KIT_COMPANY', 'Laravel Business Starter Kit'),
        'email' => env('STARTER_KIT_COMPANY_EMAIL', 'support@example.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    |
    | Behaviour of the Authentication module. Every value is intentionally
    | configurable so deployments never require code modifications.
    |
    */

    'auth' => [
        'registration_enabled' => (bool) env('AUTH_REGISTRATION_ENABLED', true),

        'email_verification_required' => (bool) env('AUTH_EMAIL_VERIFICATION_REQUIRED', true),

        'default_role' => env('AUTH_DEFAULT_ROLE', 'Viewer'),

        'password' => [
            'min_length' => (int) env('AUTH_PASSWORD_MIN_LENGTH', 10),
            'require_uppercase' => (bool) env('AUTH_PASSWORD_REQUIRE_UPPERCASE', true),
            'require_lowercase' => (bool) env('AUTH_PASSWORD_REQUIRE_LOWERCASE', true),
            'require_numbers' => (bool) env('AUTH_PASSWORD_REQUIRE_NUMBERS', true),
            'require_symbols' => (bool) env('AUTH_PASSWORD_REQUIRE_SYMBOLS', true),
        ],

        'lockout' => [
            'max_attempts' => (int) env('AUTH_LOCKOUT_MAX_ATTEMPTS', 5),
            'decay_minutes' => (int) env('AUTH_LOCKOUT_DECAY_MINUTES', 15),
        ],

        'invalidate_sessions_on_password_change' => (bool) env('AUTH_INVALIDATE_SESSIONS_ON_PASSWORD_CHANGE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    'dashboard' => [
        'cache_ttl' => (int) env('DASHBOARD_CACHE_TTL', 300),
        'recent_activity_limit' => (int) env('DASHBOARD_RECENT_ACTIVITY_LIMIT', 8),
        'recent_users_limit' => (int) env('DASHBOARD_RECENT_USERS_LIMIT', 5),
        'refresh_intervals' => [0, 60, 300, 900],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Page sizes offered by the Table System. See 13_Table_System.md §19.
    |
    */

    'pagination' => [
        'default' => 25,
        'options' => [10, 25, 50, 100],
    ],

    /*
    |--------------------------------------------------------------------------
    | Login History Retention
    |--------------------------------------------------------------------------
    */

    'login_history' => [
        'retention_days' => (int) env('LOGIN_HISTORY_RETENTION_DAYS', 90),
    ],

];
