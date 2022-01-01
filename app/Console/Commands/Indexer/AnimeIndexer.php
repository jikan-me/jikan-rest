<?php

namespace App\Console\Commands\Indexer;

use App\Exceptions\Console\CommandAlreadyRunningException;
use App\Exceptions\Console\FileNotFoundException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;


/**
 * Class AnimeIndexer
 * @package App\Console\Commands\Indexer
 */
class AnimeIndexer extends Command
{
    /**
     * The name and signature of the console command.
     *`
     * @var string
     */
    protected $signature = 'indexer:anime 
                            {--failed : Run only entries that failed to index last time}
                            {--resume : Resume from the last position}
                            {--reverse : Start from the end of the array}
                            {--index=0 : Start from a specific index}
                            {--delay=3 : Set a delay between requests}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index all anime';

    /**
     * @var array
     */
    private array $ids;

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

        $failed = $this->option('failed') ?? false;
        $resume = $this->option('resume') ?? false;
        $reverse = $this->option('reverse') ?? false;
        $delay = $this->option('delay') ?? 3;
        $index = $this->option('index') ?? 0;

        $index = (int)$index;
        $delay = (int)$delay;

        $this->info("Info: AnimeIndexer uses seanbreckenridge/mal-id-cache fetch available MAL IDs and updates/indexes them\n\n");

        if ($failed && Storage::exists('indexer/indexer_anime.save')) {
            $this->ids = $this->loadFailedMalIds();
        }

        if (!$failed) {
            $this->ids = $this->fetchMalIds();
        }

        // start from the end
        if ($reverse) {
            $this->ids = array_reverse($this->ids);
        }

        // Resume
        if ($resume && Storage::exists('indexer/indexer_anime.save')) {
            $index = (int)Storage::get('indexer/indexer_anime.save');

            $this->info("Resuming from index: {$index}");
        }

        // check if index even exists
        if ($index > 0 && !isset($this->ids[$index])) {
            $index = 0;
            $this->warn('Invalid index; set back to 0');
        }

        // initialize and index
        Storage::put('indexer/indexer_anime.save', 0);

        echo "Loading MAL IDs\n";
        $count = count($this->ids);
        $failedIds = [];
        $success = [];

        echo "{$count} entries available\n";
        for ($i = $index; $i <= ($count - 1); $i++) {
            $id = $this->ids[$i];

            $url = env('APP_URL') . "/v4/anime/{$id}";

            echo "Indexing/Updating " . ($i + 1) . "/{$count} {$url} [MAL ID: {$id}] \n";

            try {
                $response = json_decode(file_get_contents($url), true);

                if (isset($response['error']) && $response['status'] != 404) {
                    echo "[SKIPPED] Failed to fetch {$url} - {$response['error']}\n";
                    $failedIds[] = $id;
                    Storage::put('indexer/indexer_anime.failed', json_encode($failedIds));
                }

                sleep($delay);
            } catch (\Exception $e) {
                echo "[SKIPPED] Failed to fetch {$url}\n";
                $failedIds[] = $id;
                Storage::put('indexer/indexer_anime.failed', json_encode($failedIds));
            }

            $success[] = $id;
            Storage::put('indexer/indexer_anime.save', $i);
        }

        Storage::delete('indexer/indexer_anime.save');

        echo "---------\nIndexing complete\n";
        echo count($success) . " entries indexed or updated\n";
        echo count($failedIds) . " entries failed to index or update. Re-run with --failed to requeue failed entries only\n";
    }

    /**
     * @return array
     * @url https://github.com/seanbreckenridge/mal-id-cache
     */
    private function fetchMalIds() : array
    {
        $this->info("Fetching MAL ID Cache https://raw.githubusercontent.com/seanbreckenridge/mal-id-cache/master/cache/anime_cache.json...\n");

        $ids = json_decode(
            file_get_contents('https://raw.githubusercontent.com/seanbreckenridge/mal-id-cache/master/cache/anime_cache.json'),
            true
        );

        $this->ids = $ids['sfw'] + $ids['nsfw']; // merge
        Storage::put('indexer/anime_mal_id.json', json_encode($this->ids));

        return json_decode(Storage::get('indexer/anime_mal_id.json'));
    }

    /**
     * @return array
     * @throws FileNotFoundException
     */
    private function loadFailedMalIds() : array
    {
        if (!Storage::exists('indexer/indexer_anime.failed')) {
            throw new FileNotFoundException('"indexer/indexer_anime.failed" does not exist');
        }

        return json_decode(Storage::get('indexer/indexer_anime.failed'));
    }

}
