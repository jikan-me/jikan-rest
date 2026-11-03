<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Laravel\Scout\Builder;

interface SearchService
{
    public function search(string $searchTerms, ?string $orderByFields = null, bool $sortDirectionDescending = false): Builder|\Illuminate\Database\Eloquent\Builder;

    public function setFilterParameters(Collection $requestParameters): self;

    public function query(): Builder|\Illuminate\Database\Eloquent\Builder;
}
