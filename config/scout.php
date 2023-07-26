<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Search Engine
    |--------------------------------------------------------------------------
    |
    | This option controls the default search connection that gets used while
    | using Laravel Scout. This connection is used when syncing all models
    | to the search service. You should adjust this based on your needs.
    |
    | Supported: "algolia", "meilisearch", "database", "collection", "typesense", "elasticsearch", "null"
    |
    */

    'driver' => env('SCOUT_DRIVER', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Index Prefix
    |--------------------------------------------------------------------------
    |
    | Here you may specify a prefix that will be applied to all search index
    | names used by Scout. This prefix may be useful if you have multiple
    | "tenants" or applications sharing the same search infrastructure.
    |
    */

    'prefix' => env('SCOUT_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Queue Data Syncing
    |--------------------------------------------------------------------------
    |
    | This option allows you to control if the operations that sync your data
    | with your search engines are queued. When this is set to "true" then
    | all automatic data syncing will get queued for better performance.
    |
    */

    'queue' => env('SCOUT_QUEUE', false),

    /*
    |--------------------------------------------------------------------------
    | Database Transactions
    |--------------------------------------------------------------------------
    |
    | This configuration option determines if your data will only be synced
    | with your search indexes after every open database transaction has
    | been committed, thus preventing any discarded data from syncing.
    |
    */

    'after_commit' => false,

    /*
    |--------------------------------------------------------------------------
    | Chunk Sizes
    |--------------------------------------------------------------------------
    |
    | These options allow you to control the maximum chunk size when you are
    | mass importing data into the search engine. This allows you to fine
    | tune each of these chunk sizes based on the power of the servers.
    |
    */

    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Soft Deletes
    |--------------------------------------------------------------------------
    |
    | This option allows to control whether to keep soft deleted records in
    | the search indexes. Maintaining soft deleted records can be useful
    | if your application still needs to search for the records later.
    |
    */

    'soft_delete' => false,

    /*
    |--------------------------------------------------------------------------
    | Identify User
    |--------------------------------------------------------------------------
    |
    | This option allows you to control whether to notify the search engine
    | of the user performing the search. This is sometimes useful if the
    | engine supports any analytics based on this application's users.
    |
    | Supported engines: "algolia"
    |
    */

    'identify' => env('SCOUT_IDENTIFY', false),

    /*
    |--------------------------------------------------------------------------
    | Algolia Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Algolia settings. Algolia is a cloud hosted
    | search engine which works great with Scout out of the box. Just plug
    | in your application ID and admin API key to get started searching.
    |
    */

    'algolia' => [
        'id' => env('ALGOLIA_APP_ID', ''),
        'secret' => env('ALGOLIA_SECRET', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | TypeSense Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your TypeSense settings. TypeSense is an open
    | source search engine. Below, you can state
    | the host and key information for your own TypeSense installation.
    |
    | See: https://github.com/typesense/laravel-scout-typesense-driver
    | https://typesense.org/docs/
    |
    */

    'typesense' => [
        'api_key'         => env('TYPESENSE_API_KEY','abcd'),
        'nodes'           => [
            [
                'host'     => env('TYPESENSE_HOST', 'localhost'),
                'port'     => env('TYPESENSE_PORT', '8108'),
                'path'     => '',
                'protocol' => env('TYPESENSE_PROTOCOL', 'http'),
            ],
        ],
        'nearest_node'    => [
            'host'     => env('TYPESENSE_HOST', 'localhost'),
            'port'     => env('TYPESENSE_PORT', '8108'),
            'path'     => '',
            'protocol' => env('TYPESENSE_PROTOCOL', 'http'),
        ],
        'connection_timeout_seconds'   => 2,
        'healthcheck_interval_seconds' => 30,
        'num_retries'                  => 3,
        'retry_interval_seconds'       => 1,
    ],

    'elasticsearch' => [
        'host' => env('ELASTICSEARCH_HOST'),
        'indices' => [
            'mappings' => [
                'default' => [
                    'properties' => [
                        'id' => [
                            'type' => 'keyword',
                        ],
                    ],
                ],
            ],
            'settings' => [
                'default' => [
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0,
                ],
            ],
        ],
    ]
];
