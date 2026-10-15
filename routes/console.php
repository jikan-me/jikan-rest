<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console schedule
|--------------------------------------------------------------------------
| Ported from the old Lumen App\Console\Kernel::$schedule.
|
| The indexers are staggered (they all used to start at 00:00 together) and
| guarded with withoutOverlapping(). onOneServer() makes a replicated
| deployment run each indexer on a single instance; it relies on a cache store
| shared by all instances (CACHE_DRIVER=redis). With the default per-process
| `array` store it is a harmless no-op, i.e. today's behaviour.
*/

// Update scheduled anime and current season data daily
// since they're airing, they're more prone to have their information updated
Schedule::command('indexer:anime-schedule')->dailyAt('00:10')->withoutOverlapping()->onOneServer();
Schedule::command('indexer:anime-current-season')->dailyAt('00:20')->withoutOverlapping()->onOneServer();

// Update common indexes daily
Schedule::command('indexer:common')->dailyAt('00:30')->withoutOverlapping()->onOneServer();
Schedule::command('indexer:genres')->dailyAt('00:40')->withoutOverlapping()->onOneServer();
Schedule::command('indexer:producers')->dailyAt('00:50')->withoutOverlapping()->onOneServer();
Schedule::command('indexer:anime-sweep')->dailyAt('01:00')->withoutOverlapping()->onOneServer();
Schedule::command('indexer:manga-sweep')->dailyAt('01:30')->withoutOverlapping()->onOneServer();
