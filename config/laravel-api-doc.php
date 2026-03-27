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
        'docs_path' => resource_path('docs/api'),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Versioning
    |--------------------------------------------------------------------------
    |
    | Configure the API versions you want to document and support in the UI.
    |
    */

    'versions' => [
        'enabled' => false,
        'default' => 'v1',
        'list' => [
            'v1' => 'Version 1',
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

        // Exclude routes by URI pattern
        'exclude' => [
            'api/internal/*',
        ],

        // Exclude routes by route name pattern (e.g. 'admin.*')
        'exclude_names' => [],

        // Exclude routes by assigned middleware (e.g. 'auth:admin')
        'exclude_middleware' => [],

        // Note: You can also use the #[PhpNl\LaravelApiDoc\Attributes\ExcludeFromDocs]
        // attribute directly on your controller classes or methods to exclude them.

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
    | Webhooks Documentation
    |--------------------------------------------------------------------------
    |
    | Define your outgoing webhooks here. The key is the event name, and the
    | value should be the JsonResource class that represents the payload.
    |
    */

    'webhooks' => [
        // 'order.created' => \App\Http\Resources\OrderResource::class,
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
