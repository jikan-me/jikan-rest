<?php

namespace App\Http\Controllers\V4DB;

use App\Http\QueryBuilder\SearchQueryBuilderMagazine;
use App\Http\Resources\V4\MagazineCollection;
use App\Http\Resources\V4\MangaCollection;
use App\Magazine;
use App\Manga;
use Illuminate\Http\Request;

class MagazineController extends Controller
{
    /**
     *  @OA\Get(
     *     path="/magazines",
     *     operationId="getMagazines",
     *     tags={"magazines"},
     *
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *     @OA\Parameter(ref="#/components/parameters/limit"),
     *
     *     @OA\Parameter(
     *       name="q",
     *       in="query",
     *       @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *       name="order_by",
     *       in="query",
     *       @OA\Schema(ref="#/components/schemas/magazines_query_orderby")
     *     ),
     *
     *     @OA\Parameter(
     *       name="sort",
     *       in="query",
     *       @OA\Schema(ref="#/components/schemas/search_query_sort")
     *     ),
     *
     *     @OA\Parameter(
     *       name="letter",
     *       in="query",
     *       description="Return entries starting with the given letter",
     *       @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns magazines collection",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/magazines"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function main(Request $request): MagazineCollection
    {
        $maxResultsPerPage = (int) env('MAX_RESULTS_PER_PAGE', 25);

        $page = $request->get('page') ?? 1;
        $limit = $request->get('limit') ?? $maxResultsPerPage;

        $limit = (int) $limit;

        if ($limit <= 0) {
            $limit = 1;
        }

        if ($limit > $maxResultsPerPage) {
            $limit = $maxResultsPerPage;
        }

        $results = SearchQueryBuilderMagazine::query(
            $request,
            Magazine::query()
        );

        $results = $results
            ->paginate(
                $limit,
                ['*'],
                null,
                $page
            );

        return new MagazineCollection(
            $results
        );
    }
}
