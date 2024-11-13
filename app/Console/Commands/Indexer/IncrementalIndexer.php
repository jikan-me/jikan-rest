<?php

namespace App\Console\Commands\Indexer;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class IncrementalIndexer extends Command
{
    /**
     * @var bool
     */
    private bool $cancelled = false;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indexer:incremental {mediaType*}
    {--delay=3 : Set a delay between requests}
    {--resume : Resume from the last position}
    {--failed : Run only entries that failed to index last time}';

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'mediaType' => ['The media type to index.', 'Valid values: anime, manga']
        ];
    }

    private function sleep(int $milliseconds): void
    {
        $interval = 100; // check every 100 ms
        $elapsed = 0;

        while ($elapsed < $milliseconds)
        {
            if ($this->cancelled)
            {
                return;
            }

            usleep($interval * 1000);
            $elapsed += $interval;
        }
    }

    private function getExistingIds(string $mediaType): array
    {
        $existingIdsHash = "";
        $existingIdsRaw = "";

        if (Storage::exists("indexer/incremental/$mediaType.json"))
        {
            $existingIdsRaw = Storage::get("indexer/incremental/$mediaType.json");
            $existingIdsHash = sha1($existingIdsRaw);
        }

        return [$existingIdsHash, $existingIdsRaw];
    }

    private function getIdsToFetch(string $mediaType): array
    {
        $idsToFetch = [];
        [$existingIdsHash, $existingIdsRaw] = $this->getExistingIds($mediaType);

        if ($this->cancelled)
        {
            return [];
        }

        $newIdsRaw = file_get_contents("https://raw.githubusercontent.com/purarue/mal-id-cache/master/cache/${mediaType}_cache.json");
        $newIdsHash = sha1($newIdsRaw);

        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if ($this->cancelled)
        {
            return [];
        }

        if ($newIdsHash !== $existingIdsHash)
        {
            $newIds = json_decode($newIdsRaw, true);
            $existingIds = json_decode($existingIdsRaw, true);

            if (is_null($existingIds) || count($existingIds) === 0)
            {
                $idsToFetch = $newIds;
            }
            else
            {
                foreach (["sfw", "nsfw"] as $t)
                {
                    $idsToFetch[$t] = array_diff($existingIds[$t], $newIds[$t]);
                }
            }

            Storage::put("indexer/incremental/$mediaType.json.tmp", $newIdsRaw);
        }

        return $idsToFetch;
    }

    private function getFailedIdsToFetch(string $mediaType): array
    {
        return json_decode(Storage::get("indexer/incremental/{$mediaType}_failed.json"));
    }

    private function fetchIds(string $mediaType, array $idsToFetch, int $delay, bool $resume): void
    {
        $index = 0;
        $success = [];
        $failedIds = [];

        if ($resume && Storage::exists("indexer/incremental/{$mediaType}_resume.save"))
        {
            $index = (int)Storage::get("indexer/incremental/{$mediaType}_resume.save");
            $this->info("Resuming from index: $index");
        }

        $ids = array_merge($idsToFetch['sfw'], $idsToFetch['nsfw']);
        $idCount = count($ids);

        if ($index > 0 && !isset($ids[$index]))
        {
            $index = 0;
            $this->warn('Invalid index; set back to 0');
        }

        Storage::put("indexer/incremental/{$mediaType}_resume.save", 0);

        $this->info("$idCount $mediaType entries available");

        for ($i = $index; $i <= ($idCount - 1); $i++)
        {
            if ($this->cancelled)
            {
                $this->info("Cancelling...");
                return;
            }

            $id = $ids[$i];

            $url = env('APP_URL') . "/v4/$mediaType/$id";
            $this->info("Indexing/Updating " . ($i + 1) . "/$idCount $url [MAL ID: $id]");

            try
            {
                $response = json_decode(file_get_contents($url), true);
                if (!isset($response['error']) || $response['status'] == 404)
                {
                    continue;
                }

                $this->error("[SKIPPED] Failed to fetch $url - {$response['error']}");
            }
            catch (\Exception)
            {
                $this->warn("[SKIPPED] Failed to fetch $url");
                $failedIds[] = $id;
                Storage::put("indexer/incremental/$mediaType.failed", json_encode($failedIds));
                continue;
            }
            finally
            {
                $this->sleep($delay * 1000);
                if ($this->cancelled)
                {
                    $this->info("Cancelling...");
                    return;
                }
            }

            $success[] = $id;
            Storage::put("indexer/incremental/{$mediaType}_resume.save", $index);
        }

        Storage::delete("indexer/incremental/{$mediaType}_resume.save");

        $this->info("--- Indexing of $mediaType is complete.");
        $this->info(count($success) . ' entries indexed or updated.');
        if (count($failedIds) > 0)
        {
            $this->info(count($failedIds) . ' entries failed to index or update. Re-run with --failed to requeue failed entries only.');
        }

        // finalize the latest state
        Storage::move("indexer/incremental/$mediaType.json.tmp", "indexer/incremental/$mediaType.json");
    }

    public function handle(): int
    {
        // validate inputs
        $validator = Validator::make(
            [
                'mediaType' => $this->argument('mediaType'),
                'delay' => $this->option('delay'),
                'resume' => $this->option('resume'),
                'failed' => $this->option('failed')
            ],
            [
                'mediaType' => 'required|array',
                'mediaType.*' => 'in:anime,manga',
                'delay' => 'integer|min:1',
                'resume' => 'bool',
                'failed' => 'bool'
            ]
        );

        if ($validator->fails())
        {
            $this->error($validator->errors()->toJson());
            return 1;
        }

        // we want to handle signals from the OS
        $this->trap([SIGTERM, SIGQUIT, SIGINT], fn () => $this->cancelled = true);

        $resume = $this->option('resume') ?? false;
        $onlyFailed = $this->option('failed') ?? false;
        $delay = $this->option('delay') ?? 3;

        /**
         * @var $mediaTypes array
         */
        $mediaTypes = $this->argument("mediaType");

        foreach ($mediaTypes as $mediaType)
        {
            $idsToFetch = [];

            // if "--failed" option is specified just run the failed ones
            if ($onlyFailed && Storage::exists("indexer/incremental/{$mediaType}_failed.json"))
            {
                $idsToFetch["sfw"] = $this->getFailedIdsToFetch($mediaType);
            }
            else
            {
                $idsToFetch = $this->getIdsToFetch($mediaType);
            }

            if ($this->cancelled)
            {
                $this->info("Cancelling...");
                return 0;
            }

            $idCount = count($idsToFetch);
            if ($idCount === 0)
            {
                $this->info("No $mediaType entries to index");
                continue;
            }

            $this->fetchIds($mediaType, $idsToFetch, $delay, $resume);
        }

        return 0;
    }
}
