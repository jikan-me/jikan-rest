<?php

namespace App\Contracts;

use App\JikanApiModel;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Collection;

/**
 * @template T of JikanApiModel
 * @implements RepositoryQuery<T>
 */
interface Repository extends RepositoryQuery
{
    /**
     * @return T
     */
    public function createEntity();

    /**
     * @return ?T
     */
    public function getByMalId(int $id);

    public function getAllByMalId(int $id): Collection;

    public function queryByMalId(int $id): EloquentBuilder;

    public function tableName(): string;

    // fixme: this should not be here.
    //        this is here because we have the "scrape" static method on models
    public function scrape(int $id): array;

    public function insert(array $attributes): bool;
}
