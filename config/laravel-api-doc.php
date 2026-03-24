<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | API Documentation UI
    |--------------------------------------------------------------------------
    |
    | Here you can configure the look and feel of your API documentation.
    |
    */

    'ui' => [
        'title' => 'API Documentation',
        'path' => '/docs/api',
        'middleware' => ['web'],
        'theme' => [
            'primary_color' => '#3b82f6', // Indigo-500
            'background_color' => '#f8fafc',
            'sidebar_width' => '300px',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Filtering
    |--------------------------------------------------------------------------
    |
    | Configure which routes should be included in the documentation.
    |
    */

    'routes' => [
        'include' => [
            'api/*',
        ],
        'exclude' => [
            'api/internal/*',
        ],
        'middleware_filters' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security & Authentication
    |--------------------------------------------------------------------------
    |
    | Define global security strategies for your API endpoints.
    |
    */

    'security' => [
        'default_auth' => 'bearer',
        'auth_strategies' => [
            'bearer' => [
                'type' => 'http',
                'scheme' => 'bearer',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Extraction Logic
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific extraction methods.
    |
    */

    'extractors' => [
        'attributes' => true,
        'signatures' => true,
        'docblocks' => true,
        'form_requests' => true,
        'json_resources' => true,
    ],
];
