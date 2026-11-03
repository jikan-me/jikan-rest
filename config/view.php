<?php

return [
    'paths' => [
        resource_path('views'),
    ],

    // `?:` (not an env() default): an EMPTY VIEW_COMPILED_PATH= in .env must
    // still fall back, otherwise Blade throws "Please provide a valid cache path".
    'compiled' => env('VIEW_COMPILED_PATH') ?: realpath(storage_path('framework/views')),
];
