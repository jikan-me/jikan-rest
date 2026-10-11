<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        // Loop-error handling previously came from pushrbx/lumen-roadrunner
        // (removed during the Laravel migration). Add an Octane listener here
        // if worker loop errors need handling again.
    ];
}
