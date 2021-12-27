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

    const MAX_RESULTS_PER_PAGE = 25;

    /**
     *  @OA\Get(
     *     path="/magazines",
     *     operationId="getMagazines",
     *     tags={"magazines"},
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
    public function main(Request $request)
    {
        $page = $request->get('page') ?? 1;
        $limit = $request->get('limit') ?? self::MAX_RESULTS_PER_PAGE;

        if (!empty($limit)) {
            $limit = (int) $limit;

            if ($limit <= 0) {
                $limit = 1;
            }

            if ($limit > self::MAX_RESULTS_PER_PAGE) {
                $limit = self::MAX_RESULTS_PER_PAGE;
            }
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

    public function resource(Request $request, int $id)
    {
        $page = $request->get('page') ?? 1;

        $results = Manga::query()
            ->where('serializations.mal_id', $id)
            ->orderBy('title');

        $results = $results
            ->paginate(
                self::MAX_RESULTS_PER_PAGE,
                ['*'],
                null,
                $page
            );

        return new MangaCollection(
            $results
        );
    }
}
