<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use MongoDB\Collection;
use MongoDB\Laravel\Connection;

class DataQuality extends Command
{
    protected $signature = 'jikan:data-quality
                            {--json : Emit machine-readable output}
                            {--limit=5 : Sample issue ids per check}';

    protected $description = 'Report duplicate ids and broken cross-collection references';

    /** @var array<int, array<string, mixed>> */
    private array $issues = [];

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));

        foreach (['anime', 'manga', 'characters', 'people', 'producers', 'clubs', 'magazines', 'users'] as $collection) {
            $this->duplicates($collection, $limit);
        }

        $this->references('anime', 'genres', 'genres_anime', $limit);
        $this->references('anime', 'explicit_genres', 'explicit_genres_anime', $limit);
        $this->references('anime', 'themes', 'themes_anime', $limit);
        $this->references('anime', 'demographics', 'demographics_anime', $limit);
        $this->references('anime', 'producers', 'producers', $limit);
        $this->references('anime', 'studios', 'producers', $limit);
        $this->references('anime', 'licensors', 'producers', $limit);

        $this->references('manga', 'genres', 'genres_manga', $limit);
        $this->references('manga', 'explicit_genres', 'explicit_genres_manga', $limit);
        $this->references('manga', 'themes', 'themes_manga', $limit);
        $this->references('manga', 'demographics', 'demographics_manga', $limit);
        $this->references('manga', 'serializations', 'magazines', $limit);

        if ($this->option('json')) {
            $this->line(json_encode($this->issues, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } elseif ($this->issues === []) {
            $this->info('No data-quality issues found.');
        } else {
            $this->table(['Check', 'Count', 'Sample'], array_map(fn (array $issue): array => [
                $issue['check'],
                $issue['count'],
                json_encode($issue['sample']),
            ], $this->issues));
        }

        return $this->issues === [] ? 0 : 1;
    }

    private function duplicates(string $collectionName, int $limit): void
    {
        $collection = $this->collection($collectionName);
        $duplicates = $collection->aggregate([
            ['$group' => ['_id' => '$mal_id', 'count' => ['$sum' => 1]]],
            ['$match' => ['count' => ['$gt' => 1]]],
            ['$limit' => $limit],
        ])->toArray();

        if ($duplicates !== []) {
            $this->issues[] = [
                'check' => "duplicates:{$collectionName}.mal_id",
                'count' => count($duplicates),
                'sample' => array_map(fn ($row) => (int) $row['_id'], $duplicates),
            ];
        }
    }

    private function references(string $sourceCollection, string $field, string $referenceCollection, int $limit): void
    {
        $referenceIds = array_flip(array_map('intval', $this->collection($referenceCollection)->distinct('mal_id')));
        $broken = [];
        $count = 0;

        $cursor = $this->collection($sourceCollection)->find([], ['projection' => ['mal_id' => 1, $field => 1]]);
        foreach ($cursor as $document) {
            foreach ((array) ($document[$field] ?? []) as $reference) {
                $id = is_array($reference) || $reference instanceof \ArrayAccess
                    ? (int) ($reference['mal_id'] ?? 0)
                    : (int) $reference;
                if ($id > 0 && !isset($referenceIds[$id])) {
                    $count++;
                    if (count($broken) < $limit) {
                        $broken[] = ['source' => (int) $document['mal_id'], 'missing' => $id];
                    }
                }
            }
        }

        if ($count > 0) {
            $this->issues[] = [
                'check' => "broken_ref:{$sourceCollection}.{$field}->{$referenceCollection}",
                'count' => $count,
                'sample' => $broken,
            ];
        }
    }

    private function collection(string $name): Collection
    {
        /** @var Connection $connection */
        $connection = DB::connection('mongodb');

        return $connection->getDatabase()->selectCollection($name);
    }
}
