<?php

namespace App\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;
use pushrbx\LumenRoadRunner\Events\LoopErrorOccurredEvent;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        LoopErrorOccurredEvent::class => [
            \App\Listeners\PsrWorkerErrorListener::class
        ]
    ];
}
