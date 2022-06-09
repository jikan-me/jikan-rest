<?php

namespace App\Http\QueryBuilder;

use App\Http\QueryBuilder\Traits\TopQueryFilterResolver;
use Illuminate\Support\Collection;

class TopAnimeQueryBuilder extends AnimeSearchQueryBuilder
{
    use TopQueryFilterResolver;

    protected array $filterMap = ['airing', 'upcoming', 'bypopularity', 'favorite'];

    protected function buildQuery(Collection $requestParameters, \Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $results): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        $builder = parent::buildQuery($requestParameters, $results);
        $filterType = $this->mapFilter($requestParameters->get("filter"));

        // MAL formula:
        // Top All Anime sorted by rank
        // Top Airing sorted by rank
        // Top TV, Movie, OVA, ONA, Specials ordered by rank
        // Top Upcoming ordered by members
        // Most popular ordered by members
        // Most favorites ordered by most favorites

        $builder = $builder->where("rating", "!=", $this->getAdultRating());

        return match ($filterType) {
            "airing" => $builder
                ->where("airing", true)
                ->whereNotNull("rank")
                ->where("rank", ">", 0)
                ->orderBy("rank", "asc"),
            "upcoming" => $builder
                ->where('airing', true)
                ->whereNotNull('rank')
                ->where('rank', '>', 0)
                ->orderBy('rank', 'asc'),
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
        return "top_anime";
    }
}
