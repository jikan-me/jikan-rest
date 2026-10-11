<?php
namespace App\Services;
use App\JikanApiSearchableModel;
use Illuminate\Support\Collection;
use Typesense\Client as Typesense;

/**
 * A service which helps to describe a collection in Typesense based on an eloquent model.
 *
 * Uses Laravel Scout's native Typesense engine client (first-party since Laravel 13).
 */
final class TypesenseCollectionDescriptor
{
    private readonly Collection $cache;

    public function __construct(private readonly Typesense $typesense)
    {
        $this->cache = collect();
    }

    public function getSearchableAttributes(JikanApiSearchableModel $model): array
    {
        $modelSearchableAs = $model->searchableAs();
        if ($this->cache->has($modelSearchableAs)) {
            return $this->cache->get($modelSearchableAs);
        }

        $collection = $this->typesense->getCollections()->{$modelSearchableAs};
        $collectionDetails = $collection->retrieve();
        $fields = collect($collectionDetails["fields"]);
        $searchableAttributeNames = $fields->pluck("name")->all();
        $this->cache->put($modelSearchableAs, $searchableAttributeNames);

        return $searchableAttributeNames;
    }
}