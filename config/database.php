<?php

return [
    'default' => env('DB_CONNECTION', 'mongodb'),

    'connections' => [
        'mongodb' => [
            /*
            'driver' => 'mongodb',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', 27017),
            'username' => env('DB_USERNAME', ''),
            'password' => env('DB_PASSWORD', ''),
            'options' => [
                'db' => env('MONGODB_AUTHDATABASE', '')
            ]
            */
            'driver' => 'mongodb',
            'dsn'=> "mongodb://".env('DB_USERNAME', 'jikan').":".env('DB_PASSWORD', '')."@".env('MONGODB_DSN', ''),
            'database' => env('DB_DATABASE', 'jikan'),
        ]
    ],

    'redis' => [
        'client' => 'predis',
        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0
        ]
    ],

    'migrations' => 'migrations'
];
