<?php
namespace App\Services;
use Typesense\LaravelTypesense\Typesense;
use App\JikanApiSearchableModel;
use Typesense\Collection as TypesenseCollection;
use Illuminate\Support\Collection;

/**
 * A service which helps to describe a collection in Typesense based on an eloquent model
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
        /**
         * @var TypesenseCollection $collection
         */
        $collection = $this->typesense->getCollectionIndex($model);
        $collectionDetails = $collection->retrieve();
        $fields = collect($collectionDetails["fields"]);
        $searchableAttributeNames = $fields->pluck("name")->all();
        $this->cache->put($modelSearchableAs, $searchableAttributeNames);

        return $searchableAttributeNames;
    }
}