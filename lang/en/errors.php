<?php

declare(strict_types=1);

return [

    'not_found' => 'The requested resource could not be found.',
    'too_many_requests' => 'Too many requests. Please slow down and try again shortly.',
    'server_error' => 'Something went wrong on our side. Please try again.',
    'session_expired' => 'Your session expired. Please sign in again.',
    'health_ok' => 'Service is healthy.',
    'validation_failed' => 'The submitted data is invalid.',

    'retry' => 'Try again',
    'back_to_dashboard' => 'Back to dashboard',
    'back_to_home' => 'Back to sign in',

    'pages' => [
        '401' => [
            'title' => 'Authentication required',
            'message' => 'You need to sign in before you can view this page.',
        ],
        '403' => [
            'title' => 'Access denied',
            'message' => 'Your account does not have permission to view this page.',
        ],
        '404' => [
            'title' => 'Page not found',
            'message' => 'The page you are looking for may have been moved or deleted.',
        ],
        '419' => [
            'title' => 'Page expired',
            'message' => 'Your session timed out for security reasons. Please sign in again.',
        ],
        '429' => [
            'title' => 'Too many requests',
            'message' => 'You have made too many requests in a short period. Please wait a moment.',
        ],
        '500' => [
            'title' => 'Unexpected error',
            'message' => 'We hit an unexpected problem. Our team has been notified.',
        ],
        '503' => [
            'title' => 'Under maintenance',
            'message' => 'The application is temporarily unavailable while we perform maintenance.',
        ],
    ],

];
