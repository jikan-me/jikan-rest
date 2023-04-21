<?php

namespace App\Testing;

use Typesense\LaravelTypesense\Typesense;

/**
 * @codeCoverageIgnore
 */
trait ScoutFlush
{
    protected array $searchIndexModelCleanupList = [
        "App\\Anime",
        "App\\Manga",
        "App\\Character",
        "App\\GenreAnime",
        "App\\GenreManga",
        "App\\Person",
        "App\\Club",
        "App\\Magazine",
        "App\\Producers"
    ];

    public function runScoutFlush(): void
    {
        if (config("scout.driver") === "typesense") {
            /**
             * @var Typesense $typeSenseClient
             */
            $typeSenseClient = app(Typesense::class);
            // more optimized approach for quicker tests.
            foreach ($this->searchIndexModelCleanupList as $model) {
                $modelInstance = new $model;
                $collection = $typeSenseClient->getCollectionIndex($modelInstance);
                // we count items by exporting
                $items = $collection->documents->export();
                if (strlen($items) > 1) {
                    $typeSenseClient->deleteDocuments($collection, [
                        "filter_by" => "mal_id:>0",
                        "batch_size" => 500
                    ]);
                }
            }
        }
        else {
            foreach ($this->searchIndexModelCleanupList as $model) {
                $this->artisan("scout:flush", ["model" => $model]);
            }
        }
    }
}
