<?php

namespace App\Console\Commands\Indexer;

use App\Exceptions\Console\FileNotFoundException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Generic MAL-id indexer. Each id is scraped through the app's own endpoint
 * (config('app.url').'/v4/{family}/{id}'), which stores the parsed doc in
 * MongoDB — same self-scrape pattern as indexer:anime/indexer:manga.
 *
 * Id source (`--source`):
 *  - `auto` (default): character/people read the **harvested** id sets produced
 *    by `indexer:harvest` from our stored anime/manga relations (the purarue
 *    character/people caches are stale); anime/manga read purarue.
 *  - `harvest`: force the harvest collections for character/people.
 *  - `purarue`: force the legacy purarue caches.
 *
 * Clubs have no id cache; they're populated lazily (user club-list scrapes).
 */
class ItemsIndexer extends Command
{
    use FetchesWithTimeout;

    protected $signature = 'indexer:items
                            {family : anime|manga|character|people}
                            {--source=auto : auto|harvest|purarue — where to read ids from}
                            {--failed : Run only entries that failed to index last time}
                            {--resume : Resume from the last position}
                            {--reverse : Start from the end of the id list}
                            {--index=0 : Start from a specific index}
                            {--delay=3 : Seconds between requests}
                            {--timeout=30 : Seconds before a single HTTP request is abandoned}
                            {--limit=0 : Stop after N entries (0 = all)}';

    protected $description = 'Index a family of MAL entries (anime|manga|character|people) by MAL id';

    private const FAMILIES = [
        'anime' => ['cache' => 'anime_cache', 'url' => '/v4/anime/%d', 'key' => 'anime'],
        'manga' => ['cache' => 'manga_cache', 'url' => '/v4/manga/%d', 'key' => 'manga'],
        'character' => ['cache' => 'character_cache', 'url' => '/v4/characters/%d', 'key' => 'character'],
        'people' => ['cache' => 'people_cache', 'url' => '/v4/people/%d', 'key' => 'people'],
    ];

    /** @var int[] */
    private array $ids = [];

    public function handle(): int
    {
        $family = strtolower($this->argument('family'));
        if (! isset(self::FAMILIES[$family])) {
            $this->error("Unknown family '{$family}' (anime|manga|character|people)."
                ." 'club' has no id cache — populate via lazy scraping / user club lists instead.");

            return self::FAILURE;
        }
        $urlFmt = self::FAMILIES[$family]['url'];
        $key = self::FAMILIES[$family]['key'];
        $failed = (bool) $this->option('failed');
        $resume = (bool) $this->option('resume');
        $reverse = (bool) $this->option('reverse');
        $delay = (int) $this->option('delay');
        $index = (int) $this->option('index');
        $limit = (int) $this->option('limit');
        $source = strtolower((string) $this->option('source'));
        $fromHarvest = $source === 'harvest'
            || ($source === 'auto' && in_array($family, ['character', 'people'], true));

        if ($failed && Storage::exists("indexer/indexer_{$key}.failed")) {
            $this->ids = $this->loadFailedMalIds($key);
        } elseif ($fromHarvest) {
            $this->ids = $this->harvestMalIds($family);
            if ($this->ids === []) {
                $this->error("No harvested ids for '{$family}'. Run `indexer:harvest` first, or pass --source=purarue.");

                return self::FAILURE;
            }
            $this->info('Using '.count($this->ids)." harvested {$family} ids (source: harvest)");
        } elseif (! $failed) {
            $this->ids = $this->fetchMalIds($key, self::FAMILIES[$family]['cache']);
        }

        if ($reverse) {
            $this->ids = array_reverse($this->ids);
        }

        if ($resume && Storage::exists("indexer/indexer_{$key}.save")) {
            $index = max((int) Storage::get("indexer/indexer_{$key}.save"), $index);
            $this->info("Resuming from index: {$index}");
        }

        if ($index > 0 && ! isset($this->ids[$index])) {
            $index = 0;
            $this->warn('Invalid index; set back to 0');
        }

        $this->ids = array_values($this->ids);
        Storage::put("indexer/indexer_{$key}.save", '0');

        $count = count($this->ids);
        $this->info("{$count} entries available for {$family}");
        $failedIds = [];
        $success = 0;

        for ($i = $index; $i < $count; $i++) {
            if ($limit > 0 && ($i - $index) >= $limit) {
                $this->info("Reached --limit={$limit}; stopping.");
                break;
            }
            $id = (int) $this->ids[$i];
            $url = config('app.url').sprintf($urlFmt, $id);

            echo 'Indexing/Updating '.($i + 1)."/{$count} {$url} [MAL ID: {$id}] \n";

            try {
                $response = json_decode($this->httpGet($url), true);
                if (isset($response['error']) && $response['status'] != 404) {
                    echo "[SKIPPED] Failed to fetch {$url} - {$response['error']}\n";
                    $failedIds[] = $id;
                    Storage::put("indexer/indexer_{$key}.failed", json_encode($failedIds));
                } else {
                    $success++;
                }
            } catch (\Exception $e) {
                echo "[SKIPPED] Failed to fetch {$url}\n";
                $failedIds[] = $id;
                Storage::put("indexer/indexer_{$key}.failed", json_encode($failedIds));
            }

            Storage::put("indexer/indexer_{$key}.save", (string) $i);
            sleep($delay);
        }

        Storage::delete("indexer/indexer_{$key}.save");
        echo "---------\nDone. {$success} indexed; ".count($failedIds)." failed (re-run with --failed).\n";

        return self::SUCCESS;
    }

    /**
     * @return int[]
     */
    private function fetchMalIds(string $key, string $cache): array
    {
        $url = "https://raw.githubusercontent.com/purarue/mal-id-cache/master/cache/{$cache}.json";
        $this->info("Fetching {$cache} from {$url}");
        $decoded = json_decode($this->httpGet($url), true);

        $ids = $decoded['ids'] ?? array_merge($decoded['sfw'] ?? [], $decoded['nsfw'] ?? []);
        $ids = array_values(array_unique(array_map('intval', (array) $ids)));
        sort($ids, SORT_NUMERIC);
        Storage::put("indexer/{$key}_mal_id.json", json_encode($ids));

        return $ids;
    }

    /**
     * @return int[]
     */
    private function loadFailedMalIds(string $key): array
    {
        if (! Storage::exists("indexer/indexer_{$key}.failed")) {
            throw new FileNotFoundException("indexer/indexer_{$key}.failed does not exist");
        }

        return (array) json_decode((string) Storage::get("indexer/indexer_{$key}.failed"));
    }

    /**
     * Live character/person ids harvested from our stored anime/manga relations
     * by `indexer:harvest` (replaces the stale purarue caches).
     *
     * @return int[]
     */
    private function harvestMalIds(string $family): array
    {
        $path = "indexer/harvest_{$family}.json";
        if (! Storage::exists($path)) {
            return [];
        }

        return array_map('intval', (array) json_decode((string) Storage::get($path), true));
    }
}
