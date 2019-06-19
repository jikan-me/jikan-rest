<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ModifyCacheMethod extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:method {method}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change caching method. (legacy, queue)';

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
        if (!\in_array($this->argument('method'), ['legacy', 'queue'])) {
            $this->error('Invalid cache method');
            return;
        }



        $path = base_path('.env');

        if (file_exists($path)) {
            file_put_contents($path, str_replace(
                'CACHE_METHOD='.env('CACHE_METHOD'), 'CACHE_METHOD='.$this->argument('method'), file_get_contents($path)
            ));
        }

        $this->info("CACHE_METHOD is now set to {$this->argument('method')}");
    }
}
