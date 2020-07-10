<?php

namespace App\Http\QueryBuilder;

use Jenssegers\Mongodb\Eloquent\Builder;
use Illuminate\Http\Request;

interface SearchQueryBuilderInterface
{
    static function query(Request $request, Builder $results) : Builder;
//    static function paginate(Request $request, Builder $results);
}
