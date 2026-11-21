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
 *   anime producers/studios/licensors -> producer ids (read from the stored doc, no scrape)
 *
 * Ids are deduped into `storage/app/indexer/harvest_{character,people}.json`,
 * which `indexer:items character|people --source=harvest` (Phase B) then reads to
 * populate the `characters` / `people` collections.
 */
class HarvestIndexer extends Command
{
    use FetchesWithTimeout;

    protected $signature = 'indexer:harvest
                            {--kind=all : all|anime|manga|producers}
                            {--no-staff : Skip the anime /staff pass (characters + voice actors only)}
                            {--failed : Run only entries that failed last time}
                            {--resume : Resume from the last position}
                            {--reverse : Start from the end of the id list}
                            {--index=0 : Start from a specific index}
                            {--delay=2 : Seconds between requests}
                            {--timeout=30 : Seconds before a single HTTP request is abandoned}
                            {--limit=0 : Stop after N entries (0 = all)}
                            {--shard=0 : Shard number (0-based) for parallel runs}
                            {--shards=1 : Total number of shards}
                            {--merge : Merge shard harvest files into the main files and exit}';

    protected $description = 'Harvest character/person MAL ids from stored anime/manga (relational indexer, phase A)';

    /** Harvest families written to storage. */
    private const HARVEST_FAMILIES = ['character', 'people'];

    /** Kinds that can be harvested. */
    private const KINDS = ['anime', 'manga', 'producers'];

    /** Flush the harvest id files every N harvested entries. */
    private const FLUSH_EVERY = 25;

    /** @var int[] */
    private array $ids = [];

    /** Shard number (0-based) for parallel harvest runs. */
    private int $shard = 0;

    /** Total shards; 1 = single process. */
    private int $shards = 1;

    /** @var array<string, array<int, true>> family => set of harvested ids */
    private array $harvestSet = ['character' => [], 'people' => []];

    /** @var array<int, int[]> manga mal_id => author person ids */
    private array $mangaAuthors = [];

    public function handle(): int
    {
        $this->shard = max(0, (int) $this->option('shard'));
        $this->shards = max(1, (int) $this->option('shards'));
        if ($this->shard >= $this->shards) {
            $this->error("--shard must be lower than --shards ({$this->shard} >= {$this->shards}).");

            return self::FAILURE;
        }

        if ($this->option('merge')) {
            $this->mergeShards();

            return self::SUCCESS;
        }

        $kind = strtolower((string) $this->option('kind'));
        $kinds = $kind === 'all' ? self::KINDS : [$kind];
        foreach ($kinds as $k) {
            if (!in_array($k, self::KINDS, true)) {
                $this->error("Unknown kind '{$k}' (all|anime|manga|producers).");

                return self::FAILURE;
            }
        }

        $this->harvestSet = [
            'character' => $this->loadHarvestSet('character'),
            'people' => $this->loadHarvestSet('people'),
        ];

        foreach ($kinds as $k) {
            if ($k === 'producers') {
                $this->harvestProducers();

                continue;
            }
            $this->harvestKind($k);
        }

        $this->flushHarvest();

        if (array_intersect($kinds, ['anime', 'manga']) !== []) {
            $this->newLine();
            $this->info('Harvest totals: '.count($this->harvestSet['character']).' characters, '
                .count($this->harvestSet['people']).' people.');
            $this->line('Next: indexer:items character --source=harvest   (and)   indexer:items people --source=harvest');
        }

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
            $resumePath = $this->shards > 1
                ? "indexer/indexer_{$key}.shard{$this->shard}.save"
                : "indexer/indexer_{$key}.save";
            $index = max((int) Storage::get($resumePath), $index);
            $this->info("Resuming from index: {$index}");
        }

        $this->ids = array_values($this->ids);

        if ($this->shards > 1) {
            $per = (int) ceil(count($this->ids) / $this->shards);
            $this->ids = array_slice($this->ids, $this->shard * $per, $per);
            $this->info("Shard {$this->shard}/{$this->shards}: ".count($this->ids).' entries');
        }

        if ($index > 0 && !isset($this->ids[$index])) {
            $index = 0;
            $this->warn('Invalid index; set back to 0');
        }

        $savePath = $this->shards > 1
            ? "indexer/indexer_{$key}.shard{$this->shard}.save"
            : "indexer/indexer_{$key}.save";
        Storage::put($savePath, '0');

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

            Storage::put($savePath, (string) $i);
            if ($done > 0 && $done % self::FLUSH_EVERY === 0) {
                $this->flushHarvest();
            }
            sleep($delay);
        }

        Storage::delete($savePath);
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

        if ($kind === 'anime' && !$this->option('no-staff')) {
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
        if (!is_array($json)) {
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
        $path = $this->harvestPath($family);
        if (!Storage::exists($path)) {
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
            Storage::put($this->harvestPath($family), json_encode($ids));
        }
    }

    /**
     * Collect every producer/studio/licensor id referenced by a stored anime.
     *
     * No scraping: the ids are already in the anime docs. The list feeds
     * `indexer:producers --source=harvest`.
     */
    private function harvestProducers(): void
    {
        $this->newLine();
        $this->info('== Harvesting producers ==');

        $ids = [];
        foreach (Anime::query()->get(['producers', 'studios', 'licensors']) as $anime) {
            foreach (['producers', 'studios', 'licensors'] as $field) {
                foreach ($anime->{$field} ?? [] as $entry) {
                    $id = (int) ($entry['mal_id'] ?? 0);
                    if ($id > 0) {
                        $ids[$id] = true;
                    }
                }
            }
        }

        $list = array_keys($ids);
        sort($list, SORT_NUMERIC);
        Storage::put('indexer/harvest_producers.json', json_encode($list, JSON_THROW_ON_ERROR));
        $this->info('Wrote '.count($list).' producer ids to storage/app/indexer/harvest_producers.json');
        $this->line('Next: indexer:producers --source=harvest');
    }

    private function harvestPath(string $family): string
    {
        return $this->shards > 1
            ? "indexer/harvest_{$family}.shard{$this->shard}.json"
            : "indexer/harvest_{$family}.json";
    }

    /**
     * Union the main harvest files with every shard file and rewrite the main files.
     */
    private function mergeShards(): void
    {
        foreach (self::HARVEST_FAMILIES as $family) {
            $ids = [];
            $files = ["indexer/harvest_{$family}.json", ...Storage::files('indexer')];
            foreach ($files as $file) {
                if (!preg_match('/harvest_'.$family.'(\\.shard\\d+)?\\.json$/', $file)) {
                    continue;
                }
                foreach ((array) json_decode((string) Storage::get($file), true) as $id) {
                    $id = (int) $id;
                    if ($id > 0) {
                        $ids[$id] = true;
                    }
                }
            }
            $merged = array_keys($ids);
            sort($merged, SORT_NUMERIC);
            Storage::put("indexer/harvest_{$family}.json", json_encode($merged, JSON_THROW_ON_ERROR));
            $this->info("Merged {$family}: ".count($merged).' ids');
        }
    }

    /**
     * @return int[]
     */
    private function loadFailed(string $key): array
    {
        if (!Storage::exists("indexer/indexer_{$key}.failed")) {
            throw new FileNotFoundException("indexer/indexer_{$key}.failed does not exist");
        }

        return array_map('intval', (array) json_decode((string) Storage::get("indexer/indexer_{$key}.failed"), true));
    }
}
