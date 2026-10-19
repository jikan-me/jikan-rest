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
        // Worker loop errors used to be handled here by PsrWorkerErrorListener
        // (pushrbx/lumen-roadrunner). Octane's WorkerErrorOccurred listeners in
        // config/octane.php (ReportException, StopWorkerIfNecessary) replace it,
        // so no listener is needed here.
    ];
}
