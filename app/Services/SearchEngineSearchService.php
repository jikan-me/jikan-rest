<?php

namespace App\Services;

use App\Contracts\Repository;

final class SearchEngineSearchService extends SearchServiceBase
{
    public function __construct(
        private readonly ScoutSearchService $scoutSearchService,
        Repository $repository
    )
    {
        parent::__construct($repository);
    }

    public function search(string $searchTerms, ?string $orderByFields = null, bool $sortDirectionDescending = false): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $this->scoutSearchService->search($searchTerms, $orderByFields,
            $sortDirectionDescending)->query(function (\Illuminate\Database\Eloquent\Builder $query) {
                return $query->filter($this->filterParameters);
        });
    }
}
