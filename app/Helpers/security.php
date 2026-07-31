<?php

declare(strict_types=1);

use App\Http\Middleware\SecurityHeaders;

if (! function_exists('csp_nonce')) {
    /**
     * Returns the Content Security Policy nonce for the current request.
     *
     * The value is created by {@see SecurityHeaders} and bound into the
     * container, so every inline script rendered during the request shares the
     * same nonce. An empty string is returned outside the HTTP lifecycle, for
     * example while running console commands.
     */
    function csp_nonce(): string
    {
        $container = app();

        if (! $container->bound(SecurityHeaders::NONCE_KEY)) {
            return '';
        }

        return (string) $container->make(SecurityHeaders::NONCE_KEY);
    }
}
