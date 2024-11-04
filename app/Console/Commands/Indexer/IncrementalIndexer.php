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
            'mediaType' => ['The media type to index.', 'Valid values: anime, manga, character, people']
        ];
    }

    public function handle(): int
    {
        $validator = Validator::make(
            [
                'mediaType' => $this->argument('mediaType'),
                'delay' => $this->option('delay'),
                'resume' => $this->option('resume') ?? false,
                'failed' => $this->option('failed') ?? false
            ],
            [
                'mediaType' => 'required|in:anime,manga,character,people',
                'delay' => 'integer|min:1',
                'resume' => 'bool|prohibited_with:failed',
                'failed' => 'bool|prohibited_with:resume'
            ]
        );

        if ($validator->fails()) {
            $this->error($validator->errors()->toJson());
            return 1;
        }

        $this->trap(SIGTERM, fn () => $this->cancelled = true);

        $resume = $this->option('resume') ?? false;
        $onlyFailed = $this->option('failed') ?? false;
        $existingIdsHash = "";
        $existingIdsRaw = "";
        /**
         * @var $mediaTypes array
         */
        $mediaTypes = $this->argument("mediaType");

        foreach ($mediaTypes as $mediaType)
        {
            $idsToFetch = [];
            $failedIds = [];
            $success = [];

            if ($onlyFailed && Storage::exists("indexer/incremental/{$mediaType}_failed.json"))
            {
                $idsToFetch["sfw"] = json_decode(Storage::get("indexer/incremental/{$mediaType}_failed.json"));
            }
            else
            {
                if (Storage::exists("indexer/incremental/$mediaType.json"))
                {
                    $existingIdsRaw = Storage::get("indexer/incremental/$mediaType.json");
                    $existingIdsHash = sha1($existingIdsRaw);
                }

                if ($this->cancelled)
                {
                    return 127;
                }

                $newIdsRaw = file_get_contents("https://raw.githubusercontent.com/purarue/mal-id-cache/master/cache/${mediaType}_cache.json");
                $newIdsHash = sha1($newIdsRaw);

                /** @noinspection PhpConditionAlreadyCheckedInspection */
                if ($this->cancelled)
                {
                    return 127;
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
            }

            $idCount = count($idsToFetch);
            if ($idCount > 0)
            {
                $index = 0;
                if ($resume && Storage::exists("indexer/incremental/{$mediaType}_resume.save"))
                {
                    $index = (int)Storage::get("indexer/incremental/{$mediaType}_resume.save");
                    $this->info("Resuming from index: $index");
                }

                if ($index > 0 && !isset($this->ids[$index])) {
                    $index = 0;
                    $this->warn('Invalid index; set back to 0');
                }

                Storage::put("indexer/incremental/{$mediaType}_resume.save", 0);

                $this->info("$idCount $mediaType entries available");
                $ids = array_merge($idsToFetch['sfw'], $idsToFetch['nsfw']);
                for ($i = $index; $i <= ($idCount - 1); $i++)
                {
                    if ($this->cancelled)
                    {
                        return 127;
                    }

                    $id = $ids[$index];

                    $url = env('APP_URL') . "/v4/anime/$id";
                    $this->info("Indexing/Updating " . ($i + 1) . "/$idCount $url [MAL ID: $id]");

                    try
                    {
                        $response = json_decode(file_get_contents($url), true);
                        if (isset($response['error']) && $response['status'] != 404)
                        {
                            $this->error("[SKIPPED] Failed to fetch $url - {$response['error']}");
                        }
                    }
                    catch (\Exception)
                    {
                        $this->warn("[SKIPPED] Failed to fetch $url");
                        $failedIds[] = $id;
                        Storage::put("indexer/incremental/$mediaType.failed", json_encode($failedIds));
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
        }

        return 0;
    }
}
