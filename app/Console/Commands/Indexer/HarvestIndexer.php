<?php

namespace App\Console\Commands\Indexer;

use App\Anime;
use App\Exceptions\Console\FileNotFoundException;
use App\Manga;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Phase A of the relational character/people indexer.
 *
 * Walks the *already stored* anime/manga collections and harvests live
 * character/person MAL ids from their sub-resources — instead of relying on the
 * stale purarue character/people caches:
 *
 *   anime/{id}/characters -> character ids + voice-actor person ids
 *   anime/{id}/staff      -> crew person ids (unless --no-staff)
 *   manga/{id}/characters -> character ids
 *   manga.authors         -> mangaka person ids (read from the stored doc, no scrape)
 *
 * Ids are deduped into `storage/app/indexer/harvest_{character,people}.json`,
 * which `indexer:items character|people --source=harvest` (Phase B) then reads to
 * populate the `characters` / `people` collections.
 */
class HarvestIndexer extends Command
{
    use FetchesWithTimeout;

    protected $signature = 'indexer:harvest
                            {--kind=all : all|anime|manga}
                            {--no-staff : Skip the anime /staff pass (characters + voice actors only)}
                            {--failed : Run only entries that failed last time}
                            {--resume : Resume from the last position}
                            {--reverse : Start from the end of the id list}
                            {--index=0 : Start from a specific index}
                            {--delay=2 : Seconds between requests}
                            {--timeout=30 : Seconds before a single HTTP request is abandoned}
                            {--limit=0 : Stop after N entries (0 = all)}';

    protected $description = 'Harvest character/person MAL ids from stored anime/manga (relational indexer, phase A)';

    /** Harvest families written to storage. */
    private const HARVEST_FAMILIES = ['character', 'people'];

    /** Flush the harvest id files every N harvested entries. */
    private const FLUSH_EVERY = 25;

    /** @var int[] */
    private array $ids = [];

    /** @var array<string, array<int, true>> family => set of harvested ids */
    private array $harvestSet = ['character' => [], 'people' => []];

    /** @var array<int, int[]> manga mal_id => author person ids */
    private array $mangaAuthors = [];

    public function handle(): int
    {
        $kind = strtolower((string) $this->option('kind'));
        $kinds = $kind === 'all' ? ['anime', 'manga'] : [$kind];
        foreach ($kinds as $k) {
            if (! in_array($k, ['anime', 'manga'], true)) {
                $this->error("Unknown kind '{$k}' (all|anime|manga).");

                return self::FAILURE;
            }
        }

        $this->harvestSet = [
            'character' => $this->loadHarvestSet('character'),
            'people' => $this->loadHarvestSet('people'),
        ];

        foreach ($kinds as $k) {
            $this->harvestKind($k);
        }

        $this->flushHarvest();
        $this->newLine();
        $this->info('Harvest totals: '.count($this->harvestSet['character']).' characters, '
            .count($this->harvestSet['people']).' people.');
        $this->line('Next: indexer:items character --source=harvest   (and)   indexer:items people --source=harvest');

        return self::SUCCESS;
    }

    private function harvestKind(string $kind): void
    {
        $key = "harvest_{$kind}";
        $failed = (bool) $this->option('failed');
        $resume = (bool) $this->option('resume');
        $reverse = (bool) $this->option('reverse');
        $delay = (int) $this->option('delay');
        $index = (int) $this->option('index');
        $limit = (int) $this->option('limit');

        $this->newLine();
        $this->info("== Harvesting {$kind} ==");

        if ($failed && Storage::exists("indexer/indexer_{$key}.failed")) {
            $this->ids = $this->loadFailed($key);
        } else {
            $this->ids = $this->storedMalIds($kind);
            if ($kind === 'manga') {
                $this->loadMangaAuthors();
            }
        }

        if ($reverse) {
            $this->ids = array_reverse($this->ids);
        }

        if ($resume && Storage::exists("indexer/indexer_{$key}.save")) {
            $index = max((int) Storage::get("indexer/indexer_{$key}.save"), $index);
            $this->info("Resuming from index: {$index}");
        }

        $this->ids = array_values($this->ids);
        if ($index > 0 && ! isset($this->ids[$index])) {
            $index = 0;
            $this->warn('Invalid index; set back to 0');
        }

        Storage::put("indexer/indexer_{$key}.save", '0');

        $count = count($this->ids);
        $this->info("{$count} stored {$kind} entries to harvest");
        $failedIds = [];
        $done = 0;

        for ($i = $index; $i < $count; $i++) {
            if ($limit > 0 && ($i - $index) >= $limit) {
                $this->info("Reached --limit={$limit}; stopping.");
                break;
            }
            $id = (int) $this->ids[$i];
            echo 'Harvesting '.($i + 1)."/{$count} {$kind}/{$id}\n";

            try {
                [$characterIds, $peopleIds] = $this->harvestEntry($kind, $id);
                foreach ($characterIds as $cid) {
                    $this->harvestSet['character'][$cid] = true;
                }
                foreach ($peopleIds as $pid) {
                    $this->harvestSet['people'][$pid] = true;
                }
                $done++;
            } catch (\Throwable $e) {
                echo "[SKIPPED] {$kind}/{$id}: ".substr($e->getMessage(), 0, 120)."\n";
                $failedIds[] = $id;
                Storage::put("indexer/indexer_{$key}.failed", json_encode($failedIds));
            }

            Storage::put("indexer/indexer_{$key}.save", (string) $i);
            if ($done > 0 && $done % self::FLUSH_EVERY === 0) {
                $this->flushHarvest();
            }
            sleep($delay);
        }

        Storage::delete("indexer/indexer_{$key}.save");
        echo "---------\n{$kind} done. {$done} harvested; ".count($failedIds)." failed (re-run with --failed).\n";
    }

    /**
     * @return array{0: int[], 1: int[]} [character ids, person ids]
     */
    private function harvestEntry(string $kind, int $id): array
    {
        $characterIds = [];
        $peopleIds = [];

        $characters = $this->fetch("/v4/{$kind}/{$id}/characters");
        foreach ($characters['data'] ?? [] as $entry) {
            if (isset($entry['character']['mal_id'])) {
                $characterIds[] = (int) $entry['character']['mal_id'];
            }
            foreach ($entry['voice_actors'] ?? [] as $va) {
                if (isset($va['person']['mal_id'])) {
                    $peopleIds[] = (int) $va['person']['mal_id'];
                }
            }
        }

        if ($kind === 'anime' && ! $this->option('no-staff')) {
            $staff = $this->fetch("/v4/anime/{$id}/staff");
            foreach ($staff['data'] ?? [] as $entry) {
                if (isset($entry['person']['mal_id'])) {
                    $peopleIds[] = (int) $entry['person']['mal_id'];
                }
            }
        }

        if ($kind === 'manga') {
            $peopleIds = array_merge($peopleIds, $this->mangaAuthors[$id] ?? []);
        }

        return [$characterIds, $peopleIds];
    }

    /**
     * @return array<string, mixed>
     */
    private function fetch(string $path): array
    {
        $url = config('app.url').$path;
        $body = $this->httpGet($url);
        $json = json_decode($body, true);
        if (! is_array($json)) {
            throw new \RuntimeException("invalid JSON from {$url}");
        }
        if (isset($json['error']) && (int) ($json['status'] ?? 0) !== 404) {
            throw new \RuntimeException($json['error']);
        }

        return $json;
    }

    /**
     * Sorted mal_ids of every stored anime/manga doc.
     *
     * @return int[]
     */
    private function storedMalIds(string $kind): array
    {
        $model = $kind === 'anime' ? Anime::class : Manga::class;

        return $model::query()
            ->orderBy('mal_id')
            ->pluck('mal_id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    private function loadMangaAuthors(): void
    {
        foreach (Manga::query()->get(['mal_id', 'authors']) as $manga) {
            $ids = [];
            foreach ($manga->authors ?? [] as $author) {
                if (isset($author['mal_id'])) {
                    $ids[] = (int) $author['mal_id'];
                }
            }
            $this->mangaAuthors[(int) $manga->mal_id] = $ids;
        }
    }

    /**
     * @return array<int, true>
     */
    private function loadHarvestSet(string $family): array
    {
        $path = "indexer/harvest_{$family}.json";
        if (! Storage::exists($path)) {
            return [];
        }
        $ids = array_map('intval', (array) json_decode((string) Storage::get($path), true));
        $set = [];
        foreach ($ids as $id) {
            if ($id > 0) {
                $set[$id] = true;
            }
        }

        return $set;
    }

    private function flushHarvest(): void
    {
        foreach (self::HARVEST_FAMILIES as $family) {
            $ids = array_keys($this->harvestSet[$family]);
            sort($ids, SORT_NUMERIC);
            Storage::put("indexer/harvest_{$family}.json", json_encode($ids));
        }
    }

    /**
     * @return int[]
     */
    private function loadFailed(string $key): array
    {
        if (! Storage::exists("indexer/indexer_{$key}.failed")) {
            throw new FileNotFoundException("indexer/indexer_{$key}.failed does not exist");
        }

        return array_map('intval', (array) json_decode((string) Storage::get("indexer/indexer_{$key}.failed"), true));
    }
}
