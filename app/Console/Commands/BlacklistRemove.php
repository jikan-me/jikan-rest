<?php

namespace App\Console\Commands;

use App\Http\Middleware\Blacklist;
use Illuminate\Console\Command;

class BlacklistRemove extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blacklist:remove {ip} {--reload}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove an IP from the blacklist';

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
        $ip = $this->argument('ip');
        $reload = $this->option('reload');

        $blacklist = json_decode(file_get_contents(BLACKLIST_PATH), true);

        if (!\in_array($ip, $blacklist)) {
            $this->error("IP does not exist in the blacklist");
            return;
        }

        if (($key = array_search($ip, $blacklist)) !== false) {
            unset($blacklist[$key]);
        }

        file_put_contents(BLACKLIST_PATH, json_encode($blacklist));
        $this->info("Removed blacklisted IP: {$ip}");

        if ($reload) {
            Blacklist::reloadList();
            $this->info("Blacklist reloaded into Redis");
        }
    }
}
