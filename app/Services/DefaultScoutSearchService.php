<?php

namespace App\Services;

use App\Contracts\Repository;
use Laravel\Scout\Builder;

class DefaultScoutSearchService implements ScoutSearchService
{
    public function __construct(private readonly Repository $repository) {}

    public function search(string $q, ?string $orderByField = null,
        bool $sortDirectionDescending = false): Builder
    {

        return $this->repository->search($q);
    }
}
