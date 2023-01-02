<?php

namespace App\Services;

use Illuminate\Support\Collection;

interface SearchService
{
    function search(string $searchTerms, ?string $orderByFields = null, bool $sortDirectionDescending = false): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder;

    function setFilterParameters(Collection $requestParameters): self;

    function query(): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder;
}
