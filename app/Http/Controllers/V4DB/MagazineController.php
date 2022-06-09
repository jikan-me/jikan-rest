<?php

namespace App\Http\Controllers\V4DB;

use App\Http\Resources\V4\MagazineCollection;
use Illuminate\Http\Request;

class MagazineController extends ControllerWithQueryBuilderProvider
{
    /**
     *  @OA\Get(
     *     path="/magazines",
     *     operationId="getMagazines",
     *     tags={"magazines"},
     *
     *     @OA\Parameter(ref="#/components/parameters/page"),
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
     *  @OA\Schema(
     *    schema="magazines_query_orderby",
     *    description="Order by magazine data",
     *    type="string",
     *    enum={"mal_id", "name", "count"}
     *  )
     */
    public function main(Request $request)
    {
        return $this->preparePaginatedResponse(MagazineCollection::class, "magazine", $request);
    }
}
