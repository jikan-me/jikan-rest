<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\QueryAnimeReviewsCommand;
use App\Dto\QueryMangaReviewsCommand;

class ReviewsController extends Controller
{
    /**
     *  @OA\Get(
     *     path="/reviews/anime",
     *     operationId="getRecentAnimeReviews",
     *     tags={"reviews"},
     *
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns recent anime reviews",
     *         @OA\JsonContent(
     *              @OA\Schema(ref="#/components/schemas/pagination"),
     *              @OA\Schema(
     *                  @OA\Property(
     *                       property="data",
     *                       type="array",
     *
     *                       @OA\Items(
     *                           allOf={
     *                               @OA\Schema(ref="#/components/schemas/anime_review"),
     *                               @OA\Schema(
     *                                  @OA\Property(
     *                                      property="anime",
     *                                      type="object",
     *                                      ref="#/components/schemas/anime_meta",
     *                                  ),
     *                              ),
     *                               @OA\Schema(
     *                                  @OA\Property(
     *                                      property="user",
     *                                      type="object",
     *                                      ref="#/components/schemas/user_meta",
     *                                  ),
     *                              ),
     *                           }
     *                       )
     *                  ),
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function anime(QueryAnimeReviewsCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/reviews/manga",
     *     operationId="getRecentMangaReviews",
     *     tags={"reviews"},
     *
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns recent manga reviews",
     *         @OA\JsonContent(
     *
     *              @OA\Schema(ref="#/components/schemas/pagination"),
     *              @OA\Schema(
     *                  @OA\Property(
     *                       property="data",
     *                       type="array",
     *
     *                       @OA\Items(
     *                           allOf={
     *                               @OA\Schema(ref="#/components/schemas/manga_review"),
     *                               @OA\Schema(
     *                                  @OA\Property(
     *                                      property="anime",
     *                                      type="object",
     *                                      ref="#/components/schemas/manga_meta",
     *                                  ),
     *                              ),
     *                               @OA\Schema(
     *                                  @OA\Property(
     *                                      property="user",
     *                                      type="object",
     *                                      ref="#/components/schemas/user_meta",
     *                                  ),
     *                              ),
     *                           }
     *                       )
     *                  ),
     *              ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function manga(QueryMangaReviewsCommand $command)
    {
        return $this->mediator->send($command);
    }
}
