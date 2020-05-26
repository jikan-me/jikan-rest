<?php

namespace App\Providers;

use Illuminate\Support\Facades\Storage;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class SourceHealthServiceProvider extends ServiceProvider
{

    const BAD_HEALTH_STATUSES = [403, 500, 501, 502, 503, 504, 505];

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\SourceHealthEvent' => [
            'App\Listeners\SourceHealthListener',
        ],
    ];

    public static function isFailoverEnabled() : bool
    {
        return Storage::exists('source_failover.lock');
    }
}
