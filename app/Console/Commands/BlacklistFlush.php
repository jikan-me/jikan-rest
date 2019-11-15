<?php

namespace App\Console\Commands;

use App\Http\Middleware\Blacklist;
use Illuminate\Console\Command;

class BlacklistFlush extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blacklist:flush {--reload}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all IPs from blacklist';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $reload = $this->option('reload');

        file_put_contents(BLACKLIST_PATH, json_encode([]));
        $this->info("Blacklist flushed");

        if ($reload) {
            Blacklist::flushList();
            $this->info("Blacklist reloaded into Redis");
        }
    }
}
