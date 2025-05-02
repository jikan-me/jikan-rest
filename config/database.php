<?php

$db_username = env('DB_USERNAME', env("APP_ENV") === "testing" ? "" : "admin");
$dsn = "mongodb://";
if (empty($db_username)) {
    $dsn .= env('DB_HOST', 'localhost').":".env('DB_PORT', 27017)."/".env('DB_ADMIN', 'admin');
}
else {
    $dsn .= env('DB_USERNAME', 'admin').":".env('DB_PASSWORD', '')."@".env('DB_HOST', 'localhost').":".env('DB_PORT', 27017)."/".env('DB_ADMIN', 'admin');
}
$db_auth_mechanism = env('DB_AUTH_MECHANISM', '');
if (!empty($db_auth_mechanism)) {
    $dsn .= "?authMechanism=".$db_auth_mechanism;
}

return [
    'default' => env('DB_CONNECTION', 'mongodb'),

    'connections' => [
        'mongodb' => [
            'driver' => 'mongodb',
            'dsn'=> $dsn,
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
