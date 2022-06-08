<?php

namespace App\Http\QueryBuilder;

use Illuminate\Http\Request;

interface SearchQueryBuilderService
{
    function query(Request $request):  \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder;

    function paginate(Request $request, \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $results): array;

    function paginateBuilder(Request $request, \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $results): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    function getIdentifier(): string;

    function isSearchIndexUsed(): bool;
}
