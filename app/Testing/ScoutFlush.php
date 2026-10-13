<?php

namespace App\Testing;

use Typesense\Client;
use Typesense\Exceptions\TypesenseClientError;

/**
 * @codeCoverageIgnore
 */
trait ScoutFlush
{
    protected array $searchIndexModelCleanupList = [
        'App\\Anime',
        'App\\Manga',
        'App\\Character',
        'App\\GenreAnime',
        'App\\GenreManga',
        'App\\Person',
        'App\\Club',
        'App\\Magazine',
        'App\\Producers',
        'App\\Profile',
    ];

    public function setUpScoutFlush(): void
    {
        if (config('scout.driver') === 'typesense') {
            /** @var Client $typeSenseClient */
            $typeSenseClient = app(Client::class);

            foreach ($this->searchIndexModelCleanupList as $model) {
                $modelInstance = new $model;
                try {
                    $collection = $typeSenseClient->getCollections()->{$modelInstance->searchableAs()};
                    // we count items by exporting
                    $items = $collection->getDocuments()->export();
                    if (strlen($items) > 1) {
                        $collection->getDocuments()->delete([
                            'filter_by' => 'mal_id:>0',
                            'batch_size' => 500,
                        ]);

                        $collection->delete();
                    }
                } catch (TypesenseClientError $e) {
                    // A fresh environment may not have the _testing collection yet
                    // (or it was dropped during a prior test) — nothing to flush.
                }
            }
        } else {
            foreach ($this->searchIndexModelCleanupList as $model) {
                $this->artisan('scout:flush', ['model' => $model]);
            }
        }
    }
}
