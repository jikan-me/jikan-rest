<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ManageMicrocaching extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'microcaching:service {status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable or disable microcaching';

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
        if (!\in_array($this->argument('status'), ['disable', 'enable'])) {
            $this->error('Only [enable/disable] allowed');
            return;
        }

        if (!env('CACHING') || env('CACHE_DRIVER') !== 'redis') {
            $this->error('Could not enable MICROCACHING. CACHING must be set to true and CACHE_DRIVER must be redis');
        }

        $enabled = $this->argument('status') === 'enable';

        if ($enabled === env('MICROCACHING')) {
            $this->error("MICROCACHING is already '{$this->argument('status')}'");
            return;
        }

        $path = base_path('.env');

        if (!file_exists($path)) {
            $this->error(".env does not exist");
            return;
        }

        file_put_contents($path, str_replace(
            'MICROCACHING='.(env('MICROCACHING') ? 'true' : 'false'), 'MICROCACHING='.($enabled ? 'true' : 'false'), file_get_contents($path)
        ));

        $this->info("MICROCACHING: '{$this->argument('status')}'");
    }
}
