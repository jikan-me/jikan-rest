<?php

namespace App\Services;

use App\Contracts\Repository;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder;

abstract class SearchServiceBase implements SearchService
{
    protected Collection $filterParameters;

    public function __construct(protected readonly Repository $repository) {}

    public function setFilterParameters(Collection $requestParameters): SearchService
    {
        $this->filterParameters = $requestParameters;

        return $this;
    }

    public function query(): Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $this->repository->filter($this->filterParameters);
    }
}
