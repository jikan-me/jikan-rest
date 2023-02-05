<?php

namespace App\Contracts;

use App\JikanApiModel;
use Illuminate\Contracts\Database\Query\Builder;
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
    public function getByMalId(int $id): JikanApiModel|array|null;

    public function getAllByMalId(int $id): Collection;

    public function queryByMalId(int $id): Builder;

    public function tableName(): string;

    // fixme: this should not be here.
    //        this is here because we have the "scrape" static method on models
    public function scrape(int|string $id): array;

    public function insert(array $attributes): bool;

    public function random(int $numberOfRandomItems = 1): Collection;
}
