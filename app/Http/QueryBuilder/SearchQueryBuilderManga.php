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
     *   description="Manga Search Query Type",
     *   type="string",
     *   enum={"manga","novel","oneshot","doujin","manhwa","manhua"}
     * )
     */
    const MAP_TYPES = [
        'manga' => 'Manga',
        'novel' => 'Novel',
        'oneshot' => 'One-shot',
        'doujin' => 'Doujinshi',
        'manhwa' => 'Manhwa',
        'manhua' => 'Manhua'
    ];

    /**
     * @OA\Schema(
     *   schema="manga search query status",
     *   description="Manga Search Query Status",
     *   type="string",
     *   enum={"airing","complete","hiatus","discontinued","upcoming"}
     * )
     */
    const MAP_STATUS = [
        'airing' => 'Publishing',
        'complete' => 'Finished',
        'hiatus' => 'On Hiatus',
        'discontinued' => 'Discontinued',
        'upcoming' => 'Not yet published'
    ];

    /**
     * @OA\Schema(
     *   schema="manga search query orderby",
     *   description="Manga search query order_by",
     *   type="string",
     *   enum={"mal_id", "title", "start_date", "end_date", "chapters", "volumes", "score", "scored_by", "rank", "popularity", "members", "favorites"}
     * )
     */

    const ORDER_BY = [
        'mal_id', 'title', 'published.from', 'published.to', 'chapters', 'volumes', 'score', 'scored_by', 'rank', 'popularity', 'members', 'favorites'
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
        $orderBy = $request->get('order_by');
        $sort = self::mapSort($request->get('sort'));
        $letter = $request->get('letter');
        $magazine = $request->get('magazine');
        $minScore = $request->get('min_score');
        $maxScore = $request->get('max_score');

        if (!empty($query) && is_null($letter)) {

            $results = $results
                ->where('title', 'like', "%{$query}%")
                ->orWhere('title_english', 'like', "%{$query}%")
                ->orWhere('title_japanese', 'like', "%{$query}%")
                ->orWhere('title_synonyms', 'like', "%{$query}%");
        }

        if (!is_null($letter)) {
            $results = $results
                ->where('title', 'like', "{$letter}%");
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

        if (!is_null($sfw)) {
            $results = $results
                ->where('type', '!=', 'Doujinshi');
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

    public static function mapSort(?string $sort = null) : ?string
    {
        if (!is_null($sort)) {
            return null;
        }

        $sort = strtolower($sort);

        return $sort === 'desc' ? 'desc' : 'asc';
    }
}