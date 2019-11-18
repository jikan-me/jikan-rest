<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ModifyCacheDriver extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:driver {driver}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change the cache driver. [redis, file]';

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
        if (!\in_array($this->argument('driver'), ['redis', 'file'])) {
            $this->error('Invalid cache driver');
            return;
        }

        $path = base_path('.env');

        if (file_exists($path)) {
            file_put_contents($path, str_replace(
                'CACHE_DRIVER='.env('CACHE_DRIVER'), 'CACHE_DRIVER='.$this->argument('driver'), file_get_contents($path)
            ));

            $this->info("CACHE_DRIVER is now set to '{$this->argument('driver')}'");
        }
    }
}
