<?php

namespace App\Console;

use App\Console\Commands\BlacklistAdd;
use App\Console\Commands\BlacklistFlush;
use App\Console\Commands\BlacklistRemove;
use App\Console\Commands\ClearQueuedJobs;
use App\Console\Commands\CacheRemove;
use App\Console\Commands\ModifyCacheDriver;
use App\Console\Commands\ModifyCacheMethod;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use r\Queries\Writing\Delete;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ModifyCacheMethod::class,
        ModifyCacheDriver::class,
        ClearQueuedJobs::class,
        CacheRemove::class,
        BlacklistAdd::class,
        BlacklistRemove::class,
        BlacklistFlush::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }
}
