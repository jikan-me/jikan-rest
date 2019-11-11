<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class DeleteCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:delete {key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete cache by hash key';

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
        $fingerprint = $this->argument('key');

        if (!Cache::has($fingerprint)) {
            $this->error("Cache does not exist");
        }

        if (Cache::forget($fingerprint)) {
            Cache::forget("ttl:".$fingerprint);
            $this->info('Cache deleted');
        }
    }
}
