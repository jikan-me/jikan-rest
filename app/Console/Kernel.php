<?php

namespace App\Console;

use App\Console\Commands\ClearQueuedJobs;
use App\Console\Commands\CacheRemove;
use App\Console\Commands\Indexer\AnimeIndexer;
use App\Console\Commands\Indexer\AnimeScheduleIndexer;
use App\Console\Commands\Indexer\CommonIndexer;
use App\Console\Commands\Indexer\CurrentSeasonIndexer;
use App\Console\Commands\Indexer\GenreIndexer;
use App\Console\Commands\Indexer\MangaIndexer;
use App\Console\Commands\ManageMicrocaching;
use App\Console\Commands\ModifyCacheDriver;
use App\Console\Commands\ModifyCacheMethod;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

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
        CurrentSeasonIndexer::class,
        ManageMicrocaching::class,
        AnimeIndexer::class,
        MangaIndexer::class,
        GenreIndexer::class
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

        $schedule->command('indexer:genres')
            ->daily();
    }
}
