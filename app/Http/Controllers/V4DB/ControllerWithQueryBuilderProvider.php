<?php

namespace App\Http\Controllers\V4DB;

use App\Http\Controllers\V4DB\Traits\JikanApiQueryBuilder;
use App\Providers\SearchQueryBuilderProvider;
use Illuminate\Http\Request;
use Jikan\MyAnimeList\MalClient;

abstract class ControllerWithQueryBuilderProvider extends Controller
{
    use JikanApiQueryBuilder;

    private SearchQueryBuilderProvider $searchQueryBuilderProvider;

    public function __construct(Request $request, MalClient $jikan, SearchQueryBuilderProvider $searchQueryBuilderProvider)
    {
        parent::__construct($request, $jikan);
        $this->searchQueryBuilderProvider = $searchQueryBuilderProvider;
    }
}
