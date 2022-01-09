<?php

namespace App\Http\QueryBuilder;

use App\Http\HttpHelper;
use Illuminate\Http\Request;
use Jenssegers\Mongodb\Eloquent\Builder;


class SearchQueryBuilderManga implements SearchQueryBuilderInterface
{

    const MAX_RESULTS_PER_PAGE = 25;

    /**
     * @OA\Schema(
     *   schema="manga search query type",
     *   description="Available Manga types",
     *   type="string",
     *   enum={"manga","novel", "lightnovel", "oneshot","doujin","manhwa","manhua"}
     * )
     */
    const MAP_TYPES = [
        'manga' => 'Manga',
        'novel' => 'Novel',
        'lightnovel' => 'Light Novel',
        'oneshot' => 'One-shot',
        'doujin' => 'Doujinshi',
        'manhwa' => 'Manhwa',
        'manhua' => 'Manhua'
    ];

    /**
     * @OA\Schema(
     *   schema="manga search query status",
     *   description="Available Manga statuses",
     *   type="string",
     *   enum={"publishing","complete","hiatus","discontinued","upcoming"}
     * )
     */
    const MAP_STATUS = [
        'publishing' => 'Publishing',
        'complete' => 'Finished',
        'hiatus' => 'On Hiatus',
        'discontinued' => 'Discontinued',
        'upcoming' => 'Not yet published'
    ];

    /**
     * @OA\Schema(
     *   schema="manga search query orderby",
     *   description="Available Manga order_by properties",
     *   type="string",
     *   enum={"mal_id", "title", "start_date", "end_date", "chapters", "volumes", "score", "scored_by", "rank", "popularity", "members", "favorites"}
     * )
     */

    const ORDER_BY = [
        'mal_id' => 'mal_id',
        'title' => 'title',
        'start_date' => 'published.from',
        'end_date' => 'published.to',
        'chapters' => 'chapters',
        'volumes' => 'volumes',
        'score' => 'score',
        'scored_by' => 'scored_by',
        'rank' => 'rank',
        'popularity' => 'popularity',
        'members' => 'members',
        'favorites' => 'favorites'
    ];

    public static function query(Request $request, Builder $results) : Builder
    {
        $requestType = HttpHelper::requestType($request);
        $query = $request->get('q');
        $type = self::mapType($request->get('type'));
        $score = $request->get('score') ?? 0;
        $status = self::mapStatus($request->get('status'));
        $sfw = $request->get('sfw');
        $genres = $request->get('genres');
        $genresExclude = $request->get('genres_exclude');
        $orderBy = self::mapOrderBy($request->get('order_by'));
        $sort = self::mapSort($request->get('sort'));
        $letter = $request->get('letter');
        $magazine = $request->get('magazines');
        $minScore = $request->get('min_score');
        $maxScore = $request->get('max_score');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if (!empty($query) && is_null($letter)) {

            $results = $results
                ->where('title', 'like', "%{$query}%")
                ->orWhere('title_english', 'like', "%{$query}%")
                ->orWhere('title_japanese', 'like', "%{$query}%")
                ->orWhere('title_synonyms', 'like', "%{$query}%");
//            $results = $results
//                ->whereRaw([
//                    '$text' => [
//                        '$search' => $query
//                    ]
//                ]);
        }

        if (!is_null($letter)) {
            $results = $results
                ->where('title', 'like', "{$letter}%");
        }

        if (empty($query) && is_null($orderBy)) {
            $results = $results
                ->orderBy('mal_id');
        }

        if (!is_null($startDate)) {

            $startDate = explode('-', $startDate);

            $startDate = (new \DateTime())
                ->setDate(
                    $startDate[0] ?? date('Y'),
                    $startDate[1] ?? 1,
                    $startDate[2] ?? 1
                )
                ->format(\DateTimeInterface::ISO8601);

            $results = $results
                ->where('published.from', '>=', $startDate);
        }

        if (!is_null($endDate)) {

            $endDate = explode('-', $endDate);

            $endDate = (new \DateTime())
                ->setDate(
                    $endDate[0] ?? date('Y'),
                    $endDate[1] ?? 1,
                    $endDate[2] ?? 1
                )
                ->format(\DateTimeInterface::ISO8601);

            $results = $results
                ->where('published.to', '<=', $endDate);
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

        if ($minScore !== null) {
            $minScore = (float) $minScore;

            $results = $results
                ->where('score', '>=', $minScore);
        }

        if ($maxScore !== null) {
            $maxScore = (float) $maxScore;

            $results = $results
                ->where('score', '<=', $maxScore);
        }

        if (!is_null($status)) {
            $results = $results
                ->where('status', $status);
        }

        if (!is_null($magazine)) {

            $magazine = (int) $magazine;

            $results = $results
                ->where('serializations.mal_id', $magazine);
        }

        if (!is_null($genres)) {
            $genres = explode(',', $genres);

            foreach ($genres as $genre) {
                if (empty($genre)) {
                    continue;
                }

                $genre = (int) $genre;

                $results = $results
                    ->where(function($query) use ($genre) {
                        $query
                            ->where('genres.mal_id', $genre)
                            ->orWhere('demographics.mal_id', $genre)
                            ->orWhere('themes.mal_id', $genre)
                            ->orWhere('explicit_genres.mal_id', $genre);
                    });
            }
        }

        if (!is_null($genresExclude)) {
            $genresExclude = explode(',', $genresExclude);

            foreach ($genresExclude as $genreExclude) {
                if (empty($genreExclude)) {
                    continue;
                }

                $genreExclude = (int) $genreExclude;

                $results = $results
                    ->where(function($query) use ($genreExclude) {
                        $query
                            ->where('genres.mal_id', '!=', $genreExclude)
                            ->where('demographics.mal_id', '!=', $genreExclude)
                            ->where('themes.mal_id', '!=', $genreExclude)
                            ->where('explicit_genres.mal_id', '!=', $genreExclude);
                    });
                ;
            }
        }

        if (!is_null($sfw)) {
            $results = $results
                ->where('type', '!=', 'Doujinshi');
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

    /**
     * @param string|null $type
     * @return string|null
     */
    public static function mapType(?string $type = null) : ?string
    {
        $type = strtolower($type);

        return self::MAP_TYPES[$type] ?? null;
    }

    /**
     * @param string|null $status
     * @return string|null
     */
    public static function mapStatus(?string $status = null) : ?string
    {
        $status = strtolower($status);

        return self::MAP_STATUS[$status] ?? null;
    }

    /**
     * @param string|null $sort
     * @return string|null
     */
    public static function mapSort(?string $sort = null) : ?string
    {
        $sort = strtolower($sort);

        return $sort === 'desc' ? 'desc' : 'asc';
    }
    /**
     * @param string|null $orderBy
     * @return string|null
     */
    public static function mapOrderBy(?string $orderBy) : ?string
    {
        $orderBy = strtolower($orderBy);

        return self::ORDER_BY[$orderBy] ?? null;
    }
}