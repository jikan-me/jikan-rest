<?php

use App\Providers\AppServiceProvider;
use App\Providers\JikanEnumServiceProvider;
use App\Providers\SourceHeartbeatProvider;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use L5Swagger\L5SwaggerServiceProvider;

return [
    FilesystemServiceProvider::class,
    EventServiceProvider::class,
    AppServiceProvider::class,
    SourceHeartbeatProvider::class,
    JikanEnumServiceProvider::class,
    L5SwaggerServiceProvider::class,
];
