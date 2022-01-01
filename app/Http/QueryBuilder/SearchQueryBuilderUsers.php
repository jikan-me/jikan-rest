<?php

namespace App\Http\QueryBuilder;

use App\Http\HttpHelper;
use Illuminate\Http\Request;
use Jenssegers\Mongodb\Eloquent\Builder;
use Jikan\Helper\Constants as JikanConstants;
use Jikan\Request\Search\UserSearchRequest;


class SearchQueryBuilderUsers
{

    const MAX_RESULTS_PER_PAGE = 25;

    /**
     * @OA\Schema(
     *   schema="users search query gender",
     *   description="Users Search Query Gender",
     *   type="string",
     *   enum={"any","male","female","nonbinary"}
     * )
     */
    private const MAP_GENDERS = [
        'any' => JikanConstants::SEARCH_USER_GENDER_ANY,
        'male' => JikanConstants::SEARCH_USER_GENDER_MALE,
        'female' => JikanConstants::SEARCH_USER_GENDER_FEMALE,
        'nonbinary' => JikanConstants::SEARCH_USER_GENDER_NONBINARY
    ];


    public static function query(Request $request)
    {
        $page = $request->get('page') ?? 1;
        $query = $request->get('q');
        $gender = self::mapGender($request->get('gender'));
        $location = $request->get('location');
        $maxAge = $request->get('maxAge');
        $minAge = $request->get('minAge');

        return (new UserSearchRequest())
            ->setQuery($query)
            ->setGender($gender)
            ->setLocation($location)
            ->setMaxAge($maxAge)
            ->setMinAge($minAge)
            ->setPage($page);

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

    public static function mapGender(?string $type = null) : ?int
    {
        if (!is_null($type)) {
            return null;
        }

        $type = strtolower($type);

        return self::MAP_GENDERS[$type] ?? null;
    }

}