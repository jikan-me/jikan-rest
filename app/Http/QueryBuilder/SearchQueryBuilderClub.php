<?php

namespace App\Http\QueryBuilder;

use App\Http\HttpHelper;
use Illuminate\Http\Request;
use Jenssegers\Mongodb\Eloquent\Builder;


/**
 * Class SearchQueryBuilderAnime
 * @package App\Http\QueryBuilder
 */
class SearchQueryBuilderClub implements SearchQueryBuilderInterface
{

    /**
     *
     */
    const MAX_RESULTS_PER_PAGE = 25;

    /**
     * @OA\Schema(
     *   schema="club search query type",
     *   description="Club Search Query Type",
     *   type="string",
     *   enum={"public","private","secret"}
     * )
     */
    const MAP_TYPES = [
        'public' => 'public',
        'private' => 'private',
        'secret' => 'secret'
    ];

    /**
     * @OA\Schema(
     *   schema="club search query category",
     *   description="Club Search Query Category",
     *   type="string",
     *   enum={
     *      "anime","manga","actors_and_artists","characters",
     *      "cities_and_neighborhoods","companies","conventions","games",
     *      "japan","music","other","schools"
     *   }
     * )
     */
    const MAP_CATEGORY = [
        'anime' => 'Anime',
        'manga' => 'Manga',
        'actors_and_artists' => 'Actors & Artists',
        'characters' => 'Characters',
        'cities_and_neighborhoods' => 'Cities & Neighborhoods',
        'companies' => 'Companies',
        'conventions' => 'Conventions',
        'games' => 'Games',
        'japan' => 'Japan',
        'music' => 'Music',
        'other' => 'Other',
        'schools' => 'Schools'
    ];

    /**
     * @OA\Schema(
     *   schema="club search query orderby",
     *   description="Club Search Query OrderBy",
     *   type="string",
     *   enum={"mal_id","title","members_count","pictures_count","created"}
     * )
     */
    const ORDER_BY = [
        'mal_id', 'title', 'members_count', 'pictures_count', 'created'
    ];

    /**
     * @param Request $request
     * @param Builder $results
     * @return Builder
     */
    public static function query(Request $request, Builder $results) : Builder
    {
        $requestType = HttpHelper::requestType($request);
        $query = $request->get('q');
        $type = self::mapType($request->get('type'));
        $category = self::mapCategory($request->get('category'));
        $orderBy = $request->get('order_by');
        $sort = self::mapSort($request->get('sort'));
        $letter = $request->get('letter');

        if (!empty($query) && is_null($letter)) {

            $results = $results
                ->where('title', 'like', "%{$query}%");
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

        if (!is_null($category)) {
            $results = $results
                ->where('category', $category);
        }

        if (!is_null($orderBy)) {
            $results = $results
                ->orderBy($orderBy, $sort ?? 'asc');
        }

        return $results;
    }

    /**
     * @param string|null $type
     * @return string|null
     */
    public static function mapType(?string $type = null) : ?string
    {
        $type = strtolower($type);

        if (!in_array($type, self::MAP_TYPES)) {
            return null;
        }

        return $type;
    }

    /**
     * @param string|null $category
     * @return string|null
     */
    public static function mapCategory(?string $category = null) : ?string
    {
        $category = strtolower($category);

        return self::MAP_CATEGORY[$category] ?? null;
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
}