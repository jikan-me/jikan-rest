<?php

use App\Providers\AppServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\JikanEnumServiceProvider;
use App\Providers\SourceHeartbeatProvider;
use Illuminate\Filesystem\FilesystemServiceProvider;
use L5Swagger\L5SwaggerServiceProvider;

return [
    FilesystemServiceProvider::class,
    Illuminate\Foundation\Support\Providers\EventServiceProvider::class,
    AppServiceProvider::class,
    SourceHeartbeatProvider::class,
    EventServiceProvider::class,
    JikanEnumServiceProvider::class,
    L5SwaggerServiceProvider::class,
];
