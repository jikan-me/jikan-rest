<?php

namespace App\Console\Commands;

use App\Anime;
use App\Character;
use App\Club;
use App\Magazine;
use App\Manga;
use App\Person;
use App\Producers;
use App\Profile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Typesense\Client as Typesense;
use Typesense\Exceptions\TypesenseClientError;

class SearchDoctor extends Command
{
    protected $signature = 'search:doctor {--json : Emit machine-readable output}';

    protected $description = 'Check Typesense collections for the phase-4 schema and drift';

    public function handle(Typesense $typesense): int
    {
        $node = (array) config('scout.typesense.client-settings.nodes.0', []);
        $healthUrl = sprintf('%s://%s:%s/health', $node['protocol'] ?? 'http', $node['host'] ?? '127.0.0.1', $node['port'] ?? 8108);
        $context = stream_context_create(['http' => ['timeout' => 2, 'ignore_errors' => true]]);

        if (@file_get_contents($healthUrl, false, $context) === false) {
            $this->error("Typesense is not reachable at {$healthUrl}; run the Typesense service and retry.");

            return 1;
        }

        $models = [Anime::class, Manga::class, Character::class, Person::class, Producers::class, Club::class, Magazine::class, Profile::class];
        $rows = [];
        $failed = false;

        foreach ($models as $class) {
            $model = new $class;
            $collectionName = $model->searchableAs();
            $row = ['model' => class_basename($class), 'collection' => $collectionName, 'mal_id_text' => false, 'stored' => 0, 'indexed' => null, 'issue' => null];

            try {
                $collection = $typesense->getCollections()->{$collectionName}->retrieve();
                $fields = $collection['fields'] ?? [];
                $row['mal_id_text'] = in_array('mal_id_text', array_column($fields, 'name'), true);
                $row['stored'] = (int) DB::table($model->getTable())->count();

                try {
                    $result = $typesense->getCollections()->{$collectionName}->getDocuments()->search(['q' => '*', 'per_page' => 1]);
                    $row['indexed'] = (int) ($result['found'] ?? 0);
                } catch (TypesenseClientError $e) {
                    $row['issue'] = 'index search failed: '.$e->getMessage();
                }

                if (!$row['mal_id_text']) {
                    $row['issue'] = 'missing mal_id_text — reindex required';
                } elseif ($row['indexed'] !== null && $row['indexed'] !== $row['stored']) {
                    $row['issue'] = 'index/database drift';
                }
            } catch (TypesenseClientError $e) {
                $row['issue'] = 'collection unavailable: '.$e->getMessage();
            }

            if ($row['issue'] !== null) {
                $failed = true;
            }
            $rows[] = $row;
        }

        if ($this->option('json')) {
            $this->line(json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->table(
                ['Model', 'Collection', 'mal_id_text', 'Stored', 'Indexed', 'Issue'],
                array_map(fn (array $row): array => [
                    $row['model'], $row['collection'], $row['mal_id_text'] ? 'yes' : 'no',
                    $row['stored'], $row['indexed'] ?? '-', $row['issue'] ?? 'ok',
                ], $rows)
            );
        }

        return $failed ? 1 : 0;
    }
}
