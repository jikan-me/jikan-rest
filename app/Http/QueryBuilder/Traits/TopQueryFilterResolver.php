<?php

namespace App\Http\QueryBuilder\Traits;

use Illuminate\Support\Collection;

trait TopQueryFilterResolver
{
    protected array $filterMap = [];

    private function mapFilter(?string $filter = null) : ?string
    {
        $filter = strtolower($filter);

        if (!\in_array($filter, $this->filterMap)) {
            return null;
        }

        return $filter;
    }

    private function applyFilterParameter(Collection $requestParameters, \Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $builder, bool $is_manga): \Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder
    {
        $filterType = $this->mapFilter($requestParameters->get("filter"));
        $builder = $builder->where("rating", "!=", $this->getAdultRating());
        $is_running = $is_manga ? "publishing" : "airing";

        if ($filterType === "upcoming" && $is_manga) {
            $filterType = "";
        }

        // MAL formula:
        // Top All Anime sorted by rank
        // Top Airing sorted by rank
        // Top TV, Movie, OVA, ONA, Specials ordered by rank
        // Top Upcoming ordered by members
        // Most popular ordered by members
        // Most favorites ordered by most favorites

        $builder = match ($filterType) {
            $is_running => $builder
                ->where($is_running, true),
            "upcoming" => $builder
                ->where('status', 'Not yet aired')
                ->orderBy('members', 'desc'),
            "bypopularity" => $builder
                ->orderBy('members', 'desc'),
            "favorite" => $builder
                ->orderBy('favorites', 'desc'),
            default => $builder
        };

        return $builder
            ->whereNotNull('rank')
            ->where('rank', '>', 0)
            ->orderBy('rank', 'asc');
    }
}
