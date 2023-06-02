<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\QueryAnimeRecommendationsCommand;
use App\Dto\QueryMangaRecommendationsCommand;

class RecommendationsController extends Controller
{

    /**
     *  @OA\Get(
     *     path="/recommendations/anime",
     *     operationId="getRecentAnimeRecommendations",
     *     tags={"recommendations"},
     *
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns recent anime recommendations",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/recommendations"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     *
     */
    public function anime(QueryAnimeRecommendationsCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/recommendations/manga",
     *     operationId="getRecentMangaRecommendations",
     *     tags={"recommendations"},
     *
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns recent manga recommendations",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/recommendations"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     *
     */
    public function manga(QueryMangaRecommendationsCommand $command)
    {
        return $this->mediator->send($command);
    }
}
