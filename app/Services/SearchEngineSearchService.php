<?php

namespace App\Services;

use App\Contracts\Repository;
use Laravel\Scout\Builder;

final class SearchEngineSearchService extends SearchServiceBase
{
    public function __construct(
        private readonly ScoutSearchService $scoutSearchService,
        Repository $repository
    ) {
        parent::__construct($repository);
    }

    public function search(string $searchTerms, ?string $orderByFields = null, bool $sortDirectionDescending = false): Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $this->scoutSearchService->search($searchTerms, $orderByFields, $sortDirectionDescending)->filter($this->filterParameters);
    }
}
