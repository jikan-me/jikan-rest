<?php

namespace App\Console;

use App\Console\Commands\BlacklistAdd;
use App\Console\Commands\BlacklistFlush;
use App\Console\Commands\BlacklistRemove;
use App\Console\Commands\ClearQueuedJobs;
use App\Console\Commands\CacheRemove;
use App\Console\Commands\CommonIndexing;
use App\Console\Commands\Indexer\AnimeScheduleIndexer;
use App\Console\Commands\Indexer\CommonIndexer;
use App\Console\Commands\Indexer\CurrentSeasonIndexer;
use App\Console\Commands\Indexer\ScheduleIndexer;
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
        CommonIndexer::class,
        AnimeScheduleIndexer::class,
        CurrentSeasonIndexer::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Update Scheduled Anime and current season data daily
        // since they're airing, they're more prone to
        // have their information updated
        $schedule->command('indexer:anime-schedule')
            ->daily();

        $schedule->command('indexer:anime-current-season')
            ->daily();

        // Update common indexes daily
        $schedule->command('indexer:common')
            ->daily();


    }
}
