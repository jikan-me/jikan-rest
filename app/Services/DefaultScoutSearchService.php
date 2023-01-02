<?php

namespace App\Services;

use App\Contracts\Repository;

class DefaultScoutSearchService implements ScoutSearchService
{
    public function __construct(private readonly Repository $repository)
    {
    }

    public function search(string $q, ?string $orderByField = null,
                           bool $sortDirectionDescending = false): \Laravel\Scout\Builder
    {

        return $this->repository->search($q);
    }
}
