<?php

namespace App\Http\QueryBuilder;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

interface SearchQueryBuilderInterface
{
    static function query(Request $request, Builder $results) : Builder;
    static function paginate(Request $request, Builder $results);
}
