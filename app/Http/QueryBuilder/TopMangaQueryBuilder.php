<?php

namespace App\Http\QueryBuilder;

use App\Http\QueryBuilder\Traits\TopQueryFilterResolver;
use Illuminate\Support\Collection;

class TopMangaQueryBuilder extends MangaSearchQueryBuilder
{
    use TopQueryFilterResolver;

    protected array $filterMap = ['publishing', 'upcoming', 'bypopularity', 'favorite'];

    protected function buildQuery(Collection $requestParameters, \Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $results): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        $builder = parent::buildQuery($requestParameters, $results);
        $filterType = $this->mapFilter($requestParameters->get("filter"));

        $builder = $builder->where("rating", "!=", $this->getAdultRating());

        return match ($filterType) {
            "publishing" => $builder
                ->where("publishing", true)
                ->whereNotNull("rank")
                ->where("rank", ">", 0)
                ->orderBy("rank", "asc"),
            "bypopularity" => $builder
                ->orderBy('members', 'desc'),
            "favorite" => $builder
                ->orderBy('favorites', 'desc'),
            default => $builder
                ->whereNotNull('rank')
                ->where('rank', '>', 0)
                ->orderBy('rank', 'asc'),
        };
    }

    public function getIdentifier(): string
    {
        return "top_manga";
    }
}
