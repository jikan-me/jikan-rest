<?php

namespace App\Http\QueryBuilder;

use App\Http\QueryBuilder\Traits\TopMediaQueryParameterSanitizer;
use App\Http\QueryBuilder\Traits\TopQueryFilterResolver;
use App\Services\ScoutSearchService;
use Illuminate\Support\Collection;

class TopAnimeQueryBuilder extends AnimeSearchQueryBuilder
{
    use TopQueryFilterResolver, TopMediaQueryParameterSanitizer;
    protected array $parameterNames = ["producer", "producers", "rating", "filter"];

    public function __construct(bool $searchIndexesEnabled, ScoutSearchService $scoutSearchService)
    {
        parent::__construct($searchIndexesEnabled, $scoutSearchService);
        $this->filterMap = ["airing", "upcoming", "bypopularity", "favorite"];
    }

    protected function sanitizeParameters(Collection $parameters): Collection
    {
        return parent::sanitizeParameters($this->sanitizeTopMediaParameters($parameters));
    }

    protected function buildQuery(Collection $requestParameters, \Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $results): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        $builder = parent::buildQuery($requestParameters, $results);
        return $this->applyFilterParameter($requestParameters, $builder, false);
    }

    public function getIdentifier(): string
    {
        return "top_anime";
    }
}
