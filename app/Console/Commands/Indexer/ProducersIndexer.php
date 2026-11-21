<?php

namespace App\Console\Commands\Indexer;

use App\Exceptions\Console\FileNotFoundException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Jikan\Request\Producer\ProducersRequest;

/**
 * Class ProducersIndexer
 */
class ProducersIndexer extends Command
{
    /**
     * The name and signature of the console command.
     *`
     *
     * @var string
     */
    protected $signature = 'indexer:producers
                            {--source=auto : auto|harvest — read referenced producer/studio/licensor ids from the harvest file}
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
    protected $description = 'Index all producers';

    /**
     * @var array<int, int>
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
     *
     * @throws FileNotFoundException
     */
    public function handle()
    {

        $failed = $this->option('failed');
        $resume = $this->option('resume');
        $reverse = $this->option('reverse');
        $delay = $this->option('delay') ?? 3;
        $index = $this->option('index') ?? 0;

        $index = (int) $index;
        $delay = (int) $delay;

        $this->info("Info: ProducersIndexer scrapes available MAL IDs and updates/indexes them\n\n");

        if ($failed && Storage::exists('indexer/indexer_producers.save')) {
            $this->ids = $this->loadFailedMalIds();
        }

        if (!$failed) {
            $this->ids = match ((string) $this->option('source')) {
                'harvest' => $this->loadHarvestMalIds(),
                default => $this->fetchMalIds(),
            };
        }

        // start from the end
        if ($reverse) {
            $this->ids = array_reverse($this->ids);
        }

        // Resume
        if ($resume && Storage::exists('indexer/indexer_producers.save')) {
            $index = (int) Storage::get('indexer/indexer_producers.save');

            $this->info("Resuming from index: {$index}");
        }

        // check if index even exists
        if ($index > 0 && !isset($this->ids[$index])) {
            $index = 0;
            $this->warn('Invalid index; set back to 0');
        }

        // initialize and index
        Storage::put('indexer/indexer_producers.save', 0);

        echo "Loading MAL IDs\n";
        $count = count($this->ids);
        $failedIds = [];
        $success = [];

        echo "{$count} entries available\n";
        for ($i = $index; $i <= ($count - 1); $i++) {
            $id = $this->ids[$i];

            $url = config('app.url')."/v4/producers/{$id}";

            echo 'Indexing/Updating '.($i + 1)."/{$count} {$url} [MAL ID: {$id}] \n";

            try {
                $response = json_decode(file_get_contents($url), true);

                if (isset($response['error']) && $response['status'] != 404) {
                    echo "[SKIPPED] Failed to fetch {$url} - {$response['error']}\n";
                    $failedIds[] = $id;
                    Storage::put('indexer/indexer_producers.failed', json_encode($failedIds));
                }

                sleep($delay);
            } catch (\Exception $e) {
                echo "[SKIPPED] Failed to fetch {$url}\n";
                $failedIds[] = $id;
                Storage::put('indexer/indexer_producers.failed', json_encode($failedIds));
            }

            $success[] = $id;
            Storage::put('indexer/indexer_producers.save', $i);
        }

        Storage::delete('indexer/indexer_producers.save');

        echo "---------\nIndexing complete\n";
        echo count($success)." entries indexed or updated\n";
        echo count($failedIds)." entries failed to index or update. Re-run with --failed to requeue failed entries only\n";
    }

    /**
     * @return array<int, int>
     */
    private function fetchMalIds(): array
    {
        $this->info("Scraping Producer MAL IDs from https://myanimelist.net/anime/producer...\n");

        $producers = \json_decode(
            app('SerializerV4')->serialize(
                app('JikanParser')
                    ->getProducers(new ProducersRequest),
                'json'
            ),
            true
        )['producers'];

        foreach ($producers as $producer) {
            if (($producer['mal_id'] ?? null) === null) {
                continue; // producer without an id (jikan >= v5.0.0-beta.3 returns null)
            }
            $this->ids[] = $producer['mal_id'];
        }

        Storage::put('indexer/producers_mal_id.json', json_encode($this->ids));

        return json_decode(Storage::get('indexer/producers_mal_id.json'));
    }

    /**
     * Ids harvested from stored anime relations by `indexer:harvest --kind=producers`.
     *
     * @return array<int, int>
     */
    private function loadHarvestMalIds(): array
    {
        if (!Storage::exists('indexer/harvest_producers.json')) {
            throw new FileNotFoundException(
                'indexer/harvest_producers.json does not exist — run indexer:harvest --kind=producers first'
            );
        }

        return array_map('intval', (array) json_decode((string) Storage::get('indexer/harvest_producers.json'), true));
    }

    /**
     * @throws FileNotFoundException
     */
    /**
     * @return array<int, int>
     */
    private function loadFailedMalIds(): array
    {
        if (!Storage::exists('indexer/indexer_producers.failed')) {
            throw new FileNotFoundException('"indexer/indexer_producers.failed" does not exist');
        }

        return json_decode(Storage::get('indexer/indexer_producers.failed'));
    }
}
