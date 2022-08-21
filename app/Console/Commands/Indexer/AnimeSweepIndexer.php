<?php

namespace App\Console\Commands\Indexer;

use App\Exceptions\Console\CommandAlreadyRunningException;
use App\Exceptions\Console\FileNotFoundException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Class AnimeSweepIndexer
 *
 * @package App\Console\Commands\Indexer
 */
class AnimeSweepIndexer extends Command
{
    /**
     * The name and signature of the console command.
     *`
     *
     * @var string
     */
    protected $signature = 'indexer:anime-sweep';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all removed anime';

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
     * @return void
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $this->info("Info: Delete removed MAL IDs\n\n");

        echo "Loading MAL IDs\n";
        $malIds = array_fill_keys($this->fetchMalIds(), null);

        echo "Loading MAL IDs from local DB\n";
        $results = DB::table('anime')->select('mal_id', '_id')->get();

        echo "Compare MAL IDs\n";
        $remove = [];

        foreach ($results as $result) {
            if (!array_key_exists($result['mal_id'], $malIds)) {
                $remove[] = $result['_id'];
            }
        }

        echo "Delete removed MAL IDs\n";
        DB::table('anime')->whereIn('_id', $remove)->delete();
    }

    /**
     * @return array
     * @url https://github.com/seanbreckenridge/mal-id-cache
     */
    private function fetchMalIds(): array
    {
        $this->info("Fetching MAL ID Cache https://raw.githubusercontent.com/seanbreckenridge/mal-id-cache/master/cache/anime_cache.json...\n");

        $ids = json_decode(
            file_get_contents('https://raw.githubusercontent.com/seanbreckenridge/mal-id-cache/master/cache/anime_cache.json'),
            true
        );

        $ids = $ids['sfw'] + $ids['nsfw']; // merge
        Storage::put('indexer/anime_mal_id_sweep.json', json_encode($ids));

        return json_decode(Storage::get('indexer/anime_mal_id_sweep.json'));
    }
}
