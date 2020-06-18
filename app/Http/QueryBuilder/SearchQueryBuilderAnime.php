<?php

namespace App\Http\QueryBuilder;

use App\Http\HttpHelper;
use Illuminate\Http\Request;
use Jenssegers\Mongodb\Eloquent\Builder;


class SearchQueryBuilderAnime implements SearchQueryBuilderInterface
{

    const MAX_RESULTS_PER_PAGE = 50;

    const MAP_TYPES = [
        'tv' => 'TV',
        'movie' => 'Movie',
        'ova' => 'OVA',
        'special' => 'Special',
        'ona' => 'ONA',
        'music' => 'Music'
    ];

    const MAP_STATUS = [
        'airing' => 'Currently Airing',
        'complete' => 'Finished Airing',
        'upcoming' => 'Not yet aired',
    ];

    const MAP_RATING = [
        'g' => 'G - All Ages',
        'pg' => 'PG - Children',
        'pg13' => 'PG-13 - Teens 13 or older',
        'r17' => 'R - 17+ (violence & profanity)',
        'r' => 'R+ - Mild Nudity',
        'rx' => 'Rx - Hentai'
    ];

    const ORDER_BY = [
        'mal_id', 'title', 'aired.from', 'aired.to', 'episodes', 'score', 'scored_by', 'rank', 'popularity', 'members', 'favorites'
    ];

    public static function query(Request $request, Builder $results) : Builder
    {
        $requestType = HttpHelper::requestType($request);
        $query = $request->get('q');
        $type = self::mapType($request->get('type'));
        $score = $request->get('score') ?? 0;
        $status = self::mapStatus($request->get('status'));
        $rating = self::mapRating($request->get('rating'));
        $sfw = $request->get('sfw');
        $genres = $request->get('genre');
        $orderBy = $request->get('order_by');
        $sort = self::mapSort($request->get('sort'));


        if (!empty($query)) {

            $results = $results
                ->where('title', 'like', "%{$query}%")
                ->where('title_english', 'like', "%{$query}%")
                ->where('title_japanese', 'like', "%{$query}%")
                ->where('title_synonyms', 'like', "%{$query}%");
        }

        if (empty($query)) {
            $results = $results
                ->orderBy('mal_id');
        }

        if (!is_null($type)) {
            $results = $results
                ->where('type', $type);
        }

        if ($score !== 0) {
            $score = (float) $score;

            $results = $results
                ->where('score', '>=', $score);
        }

        if (!is_null($status)) {
            $results = $results
                ->where('status', $status);
        }

        if (!is_null($rating)) {
            $results = $results
                ->where('rating', $rating);
        }

        if (!is_null($sfw)) {
            $results = $results
                ->where('rating', '!=', self::MAP_RATING['rx']);
        }

        if (!is_null($genres)) {
            $genres = explode(',', $genres);

            foreach ($genres as $genre) {
                if (empty($genre)) {
                    continue;
                }

                $genre = (int) $genre;

                $results = $results
                    ->where('genres.mal_id', $genre);
            }
        }

        if (!is_null($orderBy)) {
            $results = $results
                ->orderBy($orderBy, $sort ?? 'asc');
        }

        return $results;
    }

    public static function paginate(Request $request, Builder $results)
    {
        $page = $request->get('page') ?? 1;
        $limit = $request->get('limit') ?? self::MAX_RESULTS_PER_PAGE;

        $limit = (int) $limit;

        if ($limit <= 0) {
            $limit = 1;
        }

        if ($limit > self::MAX_RESULTS_PER_PAGE) {
            $limit = self::MAX_RESULTS_PER_PAGE;
        }

        if ($page <= 0) {
            $page = 1;
        }

        $paginated = $results
            ->paginate(
                $limit,
                null,
                null,
                $page
            );

        $items = $paginated->items();
        foreach ($items as &$item) {
            unset($item['_id']);
        }

        return [
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'data' => $items
        ];
    }

    public static function mapType(?string $type = null) : ?string
    {
        if (!is_null($type)) {
            return null;
        }

        $type = strtolower($type);

        return self::MAP_TYPES[$type] ?? null;
    }

    public static function mapStatus(?string $status = null) : ?string
    {
        if (!is_null($status)) {
            return null;
        }

        $status = strtolower($status);

        return self::MAP_STATUS[$status] ?? null;
    }

    public static function mapRating(?string $rating = null) : ?string
    {
        if (!is_null($rating)) {
            return null;
        }

        $rating = strtolower($rating);

        return self::MAP_RATING[$rating] ?? null;
    }

    public static function mapSort(?string $sort = null) : ?string
    {
        if (!is_null($sort)) {
            return null;
        }

        $sort = strtolower($sort);

        return $sort === 'desc' ? 'desc' : 'asc';
    }
}