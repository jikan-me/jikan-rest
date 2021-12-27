<?php

namespace App\Http\QueryBuilder;

use App\Http\HttpHelper;
use Illuminate\Http\Request;
use Jenssegers\Mongodb\Eloquent\Builder;


/**
 * Class SearchQueryBuilderMagazine
 * @package App\Http\QueryBuilder
 *
 *  @OA\Schema(
 *    schema="magazines query orderby",
 *    description="Order by magazine data",
 *    type="string",
 *    enum={"mal_id", "name", "count"}
 *  )
 */
class SearchQueryBuilderMagazine implements SearchQueryBuilderInterface
{

    const ORDER_BY = [
        'mal_id', 'name', 'count'
    ];

    public static function query(Request $request, Builder $results) : Builder
    {
        $query = $request->get('q');
        $orderBy = $request->get('order_by');
        $sort = self::mapSort($request->get('sort'));
        $letter = $request->get('letter');


        if (!empty($query) && is_null($letter)) {

            $results = $results
                ->where('name', 'like', "%{$query}%");
        }

        if (!is_null($letter)) {
            $results = $results
                ->where('name', 'like', "{$letter}%");
        }

        if (!is_null($orderBy)) {
            $results = $results
                ->orderBy($orderBy, $sort ?? 'asc');
        }

        if (empty($query)) {
            $results = $results
                ->orderBy('mal_id');
        }

        return $results;
    }

    /**
     * @param string|null $sort
     * @return string|null
     */
    public static function mapSort(?string $sort = null) : ?string
    {
        if (is_null($sort)) {
            return null;
        }

        $sort = strtolower($sort);

        return $sort === 'desc' ? 'desc' : 'asc';
    }
}