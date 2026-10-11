<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\QueryAnimeReviewsCommand;
use App\Dto\QueryMangaReviewsCommand;
use OpenApi\Attributes as OA;

class ReviewsController extends Controller
{
    /**
     *  @OA\
     * Get(
     *     path="/reviews/anime",
     *     operationId="getRecentAnimeReviews",
     *     summary="Recent anime reviews",
     *     description="Retrieves the most recent recent anime reviews.",
     *     tags={"reviews"},
     *
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *     @OA\Parameter(ref="#/components/parameters/preliminary"),
     *     @OA\Parameter(ref="#/components/parameters/spoilers"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns recent anime reviews",
     *
     *         @OA\JsonContent(
     *
     *              @OA\Schema(ref="#/components/schemas/pagination"),
     *              @OA\Schema(
     *
     *                  @OA\Property(
     *                       property="data",
     *                       type="array",
     *
     *                       @OA\Items(
     *                           allOf={
     *
     *                               @OA\Schema(ref="#/components/schemas/anime_review"),
     *                               @OA\Schema(
     *
     *                                  @OA\Property(
     *                                      property="anime",
     *                                      type="object",
     *                                      ref="#/components/schemas/anime_meta",
     *                                  ),
     *                              ),
     *
     *                               @OA\Schema(
     *
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
     *
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    #[OA\Get(
        path: '/reviews/anime',
        operationId: 'getRecentAnimeReviews',
        summary: 'Recent anime reviews',
        description: 'Retrieves the most recent recent anime reviews.',
        tags: ['reviews'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/preliminary'),
            new OA\Parameter(ref: '#/components/parameters/spoilers'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns recent anime reviews',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/pagination'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(allOf: [new OA\Schema(ref: '#/components/schemas/anime_review')]),
                                ),
                            ],
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function anime(QueryAnimeReviewsCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/reviews/manga',
        operationId: 'getRecentMangaReviews',
        summary: 'Recent manga reviews',
        description: 'Retrieves the most recent recent manga reviews.',
        tags: ['reviews'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/preliminary'),
            new OA\Parameter(ref: '#/components/parameters/spoilers'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns recent manga reviews',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/pagination'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(allOf: [new OA\Schema(ref: '#/components/schemas/manga_review'), new OA\Schema(properties: [new OA\Property(property: 'anime', type: 'object', ref: '#/components/schemas/manga_meta')]), new OA\Schema(properties: [new OA\Property(property: 'user', type: 'object', ref: '#/components/schemas/user_meta')])]),
                                ),
                            ],
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function manga(QueryMangaReviewsCommand $command): mixed
    {
        return $this->mediator->send($command);
    }
}
