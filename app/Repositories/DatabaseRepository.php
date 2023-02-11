<?php

namespace App\Repositories;

use App\Contracts\Repository;
use App\JikanApiModel;
use App\Support\RepositoryQuery;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Collection;

class DatabaseRepository extends RepositoryQuery implements Repository
{
    /**
     * @inheritDoc
     */
    public function createEntity()
    {
        return $this->queryable()->newModelInstance();
    }

    public function getByMalId(int $id): JikanApiModel|array|null
    {
        $results = $this->getAllByMalId($id);

        return $results->isEmpty() ? null : $results->first();
    }

    public function getAllByMalId(int $id): Collection
    {
        return $this->queryByMalId($id)
            ->get();
    }

    public function queryByMalId(int $id): Builder
    {
        return $this->queryable(true)
            ->where('mal_id', $id);
    }

    public function tableName(): string
    {
        return $this->queryable(true)->newModelInstance()->getTable();
    }

    // fixme: this should not be here.
    //        this is here because we have the "scrape" static method on models
    public function scrape(int|string $id): array
    {
        $modelClass = $this->getModelClass();

        /** @noinspection PhpUndefinedMethodInspection */
        return $modelClass::scrape($id);
    }

    public function insert(array $attributes): bool
    {
        // this way we trigger scout to index records in the search index.
        $modelClass = $this->getModelClass();
        /** @noinspection PhpUndefinedMethodInspection */
        $modelInstance = new $modelClass();
        // fixme: change this to $modelClass::create() call, because that one protects against
        //        mass assignment. This way of doing it is just a work around to make sure all attributes
        //        land in the database. The "fillable" and "guard" fields of models should be populated
        //        correctly.
        $modelInstance->fill($attributes);
        foreach(["request_hash", "modifiedAt", "createdAt"] as $metaField) {
            if (array_key_exists($metaField, $attributes)) {
                $modelInstance->$metaField = $attributes[$metaField];
            }
        }

        $modelInstance->save();

        return true;
    }

    public function random(int $numberOfRandomItems = 1): Collection
    {
        return $this->queryable(true)->random($numberOfRandomItems);
    }

    private function getModelClass(): string
    {
        return get_class($this->queryable(true)->newModelInstance());
    }
}
