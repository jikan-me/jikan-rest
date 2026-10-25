<?php

return [
    'default' => 'default',
    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'Jikan REST API v4',
            ],

            'routes' => [
                'api' => 'api/documentation',
                'docs' => 'docs/openapi',
            ],

            'paths' => [
                'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', true),
                'swagger_ui_assets_path' => env('L5_SWAGGER_UI_ASSETS_PATH', 'vendor/swagger-api/swagger-ui/dist/'),
                'docs_json' => 'api-docs.json',
                'docs_yaml' => 'api-docs.yaml',
                'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),
                'annotations' => [
                    base_path('app/Http/Controllers'),
                    base_path('app/Dto'),
                    base_path('app/Http/Resources'),
                    base_path('app/Enums'),
                    base_path('app/Swagger.php'),
                ],
                'excludes' => [],
                'base' => env('L5_SWAGGER_BASE_PATH', null),
                'docs' => storage_path('api-docs'),
                'views' => base_path('resources/views/vendor/l5-swagger'),
            ],

            'securityDefinitions' => [
                'securitySchemes' => [],
                'security' => [],
            ],

            'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', false),
            'generate_yaml_copy' => env('L5_SWAGGER_GENERATE_YAML_COPY', false),

            'proxy' => env('L5_SWAGGER_PROXY', false),

            'additional_config_url' => null,
            'operations_sort' => env('L5_SWAGGER_OPERATIONS_SORT', null),

            'validator_url' => null,

            'constants' => [
                'L5_SWAGGER_CONST_HOST' => rtrim((string) env('APP_URL', 'http://localhost:8000'), '/').'/v4',
            ],
        ],
    ],

    'defaults' => [
        'routes' => [
            'docs' => 'docs',
            'oauth2_callback' => 'api/oauth2-callback',
            'middleware' => [
                'api' => [],
                'asset' => [],
                'docs' => [],
                'oauth2_callback' => [],
            ],
            'group_options' => [
                'prefix' => 'v4',
            ],
        ],

        'paths' => [
            'docs' => storage_path('api-docs'),
            'views' => base_path('resources/views/vendor/l5-swagger'),
            'base' => env('L5_SWAGGER_BASE_PATH', null),
            'scanOptions' => [
                'exclude' => [
                    base_path('vendor'),
                    base_path('tests'),
                    base_path('storage'),
                    base_path('bootstrap'),
                    base_path('config'),
                    base_path('database'),
                    base_path('resources'),
                    base_path('routes'),
                ],
            ],
        ],

        'securityDefinitions' => [
            'securitySchemes' => [],
            'security' => [],
        ],
    ],
];
