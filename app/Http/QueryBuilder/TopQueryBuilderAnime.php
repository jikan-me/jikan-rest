<?php

namespace App\Http\QueryBuilder;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Eloquent\Builder;


/**
 * Class SearchQueryBuilderAnime
 * @package App\Http\QueryBuilder
 */
class TopQueryBuilderAnime implements SearchQueryBuilderInterface
{

    /**
     *
     */
    const MAP_TYPES = [
        'tv' => 'TV',
        'movie' => 'Movie',
        'ova' => 'OVA',
        'special' => 'Special',
        'ona' => 'ONA',
        'music' => 'Music',
    ];

    /**
     *
     */
    const MAP_FILTER = [
        'airing', 'upcoming', 'bypopularity', 'favorite'
    ];


    /**
     * @param string|null $type
     * @return string|null
     */
    public static function mapType(?string $type = null) : ?string
    {
        if (is_null($type)) {
            return null;
        }

        $type = strtolower($type);

        return self::MAP_TYPES[$type] ?? null;
    }

    /**
     * @param Request $request
     * @param Builder $builder
     * @return Builder
     */
    public static function query(Request $request, Builder $results) : Builder
    {
        $animeType = self::mapType($request->get('type'));
        $filterType = self::mapFilter($request->get('filter'));

        // MAL formula:
        // Top All Anime sorted by rank
        // Top Airing sorted by rank
        // Top TV, Movie, OVA, ONA, Specials ordered by rank
        // Top Upcoming ordered by members
        // Most popular ordered by members
        // Most favorites ordered by most favorites

        $results = $results
            ->where('rating', '!=', 'Rx - Hentai');

        if (!is_null($animeType)) {
            $results = $results
                ->where('type', $animeType);
        }

        if (!is_null($filterType) && $filterType === 'airing') {
            $results = $results
                ->where('airing', true)
                ->whereNotNull('rank')
                ->where('rank', '>', 0)
                ->orderBy('rank', 'asc');

            return $results;
        }

        if (!is_null($filterType) && $filterType === 'upcoming') {
            $results = $results
                ->where('status', 'Not yet aired')
                ->orderBy('members', 'desc');

            return $results;
        }

        if (!is_null($filterType) && $filterType === 'bypopularity') {
            $results = $results
                ->orderBy('members', 'desc');

            return $results;
        }

        if (!is_null($filterType) && $filterType === 'favorite') {
            $results = $results
                ->orderBy('favorites', 'desc');

            return $results;
        }

        $results = $results
            ->whereNotNull('rank')
            ->where('rank', '>', 0)
            ->orderBy('rank', 'asc');

        return $results;
    }


    /**
     * @param string|null $filter
     * @return string|null
     */
    public static function mapFilter(?string $filter = null) : ?string
    {
        $filter = strtolower($filter);

        if (!\in_array($filter, self::MAP_FILTER)) {
            return null;
        }

        return $filter;
    }
}