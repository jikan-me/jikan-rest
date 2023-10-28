<?php

namespace App\Console;

use App\Console\Commands\CacheRemove;
use App\Console\Commands\Indexer;
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
        CacheRemove::class,
        Indexer\CommonIndexer::class,
        Indexer\AnimeScheduleIndexer::class,
        Indexer\CurrentSeasonIndexer::class,
        Indexer\AnimeIndexer::class,
        Indexer\MangaIndexer::class,
        Indexer\GenreIndexer::class,
        Indexer\ProducersIndexer::class,
        Indexer\AnimeSweepIndexer::class,
        Indexer\MangaSweepIndexer::class
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

        $schedule->command('indexer:producers')
            ->daily();

        $schedule->command('indexer:anime-sweep')
            ->daily();

        $schedule->command('indexer:manga-sweep')
            ->daily();

    }
}
