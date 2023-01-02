<?php

namespace App\Services;

use App\Contracts\Repository;
use Illuminate\Support\Collection;

abstract class SearchServiceBase implements SearchService
{
    protected Collection $filterParameters;

    /**
     * @throws \Exception
     */
    public function __construct(protected readonly Repository $repository)
    {
    }

    public function setFilterParameters(Collection $requestParameters): SearchService
    {
        $this->filterParameters = $requestParameters;
        return $this;
    }

    public function query(): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $this->repository->filter($this->filterParameters);
    }
}
