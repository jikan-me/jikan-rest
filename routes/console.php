<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console schedule
|--------------------------------------------------------------------------
| Ported from the old Lumen App\Console\Kernel::$schedule.
*/

// Update scheduled anime and current season data daily
// since they're airing, they're more prone to have their information updated
Schedule::command('indexer:anime-schedule')->daily();
Schedule::command('indexer:anime-current-season')->daily();

// Update common indexes daily
Schedule::command('indexer:common')->daily();
Schedule::command('indexer:genres')->daily();
Schedule::command('indexer:producers')->daily();
Schedule::command('indexer:anime-sweep')->daily();
Schedule::command('indexer:manga-sweep')->daily();
