<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [
        // Note: root kept at storage/app to preserve the previous Lumen behaviour
        // (e.g. Storage::exists('source_failover.lock')).
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],
    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
