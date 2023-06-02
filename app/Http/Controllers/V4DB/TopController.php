<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\QueryTopAnimeItemsCommand;
use App\Dto\QueryTopCharactersCommand;
use App\Dto\QueryTopMangaItemsCommand;
use App\Dto\QueryTopPeopleCommand;
use App\Dto\QueryTopReviewsCommand;

class TopController extends Controller
{

    /**
     *  @OA\Get(
     *     path="/top/anime",
     *     operationId="getTopAnime",
     *     tags={"top"},
     *
     *     @OA\Parameter(
     *          name="type",
     *          in="query",
     *          required=false,
     *          @OA\Schema(ref="#/components/schemas/anime_search_query_type")
     *     ),
     *
     *      @OA\Parameter(
     *          name="filter",
     *          in="query",
     *          required=false,
     *          @OA\Schema(ref="#/components/schemas/top_anime_filter")
     *      ),
     *
     *     @OA\Parameter(
     *       name="rating",
     *       in="query",
     *       @OA\Schema(ref="#/components/schemas/anime_search_query_rating")
     *     ),
     *
     *     @OA\Parameter(
     *       name="sfw",
     *       in="query",
     *       description="Filter out Adult entries",
     *       @OA\Schema(type="boolean")
     *     ),
     *
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *     @OA\Parameter(ref="#/components/parameters/limit"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns top anime",
     *          @OA\JsonContent(
     *               ref="#/components/schemas/anime_search"
     *          )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function anime(QueryTopAnimeItemsCommand $request)
    {
        return $this->mediator->send($request);
    }

    /**
     *  @OA\Get(
     *     path="/top/manga",
     *     operationId="getTopManga",
     *     tags={"top"},
     *
     *     @OA\Parameter(
     *       name="type",
     *       in="query",
     *       required=false,
     *       @OA\Schema(ref="#/components/schemas/manga_search_query_type")
     *     ),
     *
     *      @OA\Parameter(
     *          name="filter",
     *          in="query",
     *          required=false,
     *          @OA\Schema(ref="#/components/schemas/top_manga_filter")
     *      ),
     *
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *     @OA\Parameter(ref="#/components/parameters/limit"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns top manga",
     *          @OA\JsonContent(
     *               ref="#/components/schemas/manga_search"
     *          )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function manga(QueryTopMangaItemsCommand $request)
    {
        return $this->mediator->send($request);
    }

    /**
     *  @OA\Get(
     *     path="/top/people",
     *     operationId="getTopPeople",
     *     tags={"top"},
     *
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *     @OA\Parameter(ref="#/components/parameters/limit"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns top people",
     *          @OA\JsonContent(
     *               ref="#/components/schemas/people_search"
     *          )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function people(QueryTopPeopleCommand $request)
    {
        return $this->mediator->send($request);
    }

    /**
     *  @OA\Get(
     *     path="/top/characters",
     *     operationId="getTopCharacters",
     *     tags={"top"},
     *
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *     @OA\Parameter(ref="#/components/parameters/limit"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns top characters",
     *          @OA\JsonContent(
     *               ref="#/components/schemas/characters_search"
     *          )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function characters(QueryTopCharactersCommand $request)
    {
        return $this->mediator->send($request);
    }

    /**
     *  @OA\Get(
     *     path="/top/reviews",
     *     operationId="getTopReviews",
     *     tags={"top"},
     *
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *
     *     @OA\Parameter(
     *          name="type",
     *          in="query",
     *          required=false,
     *          @OA\Schema(ref="#/components/schemas/top_reviews_type_enum")
     *     ),
     *
     *     @OA\Parameter(
     *          name="preliminary",
     *          in="query",
     *          required=false,
     *          description="Whether the results include preliminary reviews or not. Defaults to true.",
     *          @OA\Schema(type="boolean")
     *     ),
     *
     *     @OA\Parameter(
     *          name="spoilers",
     *          in="query",
     *          required=false,
     *          description="Whether the results include reviews with spoilers or not. Defaults to true.",
     *          @OA\Schema(type="boolean")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns top reviews",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  allOf={
     *                      @OA\Schema(ref="#/components/schemas/pagination"),
     *                      @OA\Schema(
     *                          @OA\Property(
     *                               property="data",
     *                               type="array",
     *
     *                               @OA\Items(
     *                                   anyOf={
     *                                       @OA\Schema(
     *                                          allOf={
     *                                              @OA\Schema(ref="#/components/schemas/anime_review"),
     *                                              @OA\Schema(
     *                                                 @OA\Property(
     *                                                     property="anime",
     *                                                     type="object",
     *                                                     ref="#/components/schemas/anime_meta",
     *                                                 ),
     *                                             ),
     *                                              @OA\Schema(
     *                                                 @OA\Property(
     *                                                     property="user",
     *                                                     type="object",
     *                                                     ref="#/components/schemas/user_meta",
     *                                                 ),
     *                                             ),
     *                                          },
     *                                       ),
     *                                       @OA\Schema(
     *                                          allOf={
     *                                              @OA\Schema(ref="#/components/schemas/manga_review"),
     *                                              @OA\Schema(
     *                                                 @OA\Property(
     *                                                     property="manga",
     *                                                     type="object",
     *                                                     ref="#/components/schemas/manga_meta",
     *                                                 ),
     *                                             ),
     *                                              @OA\Schema(
     *                                                 @OA\Property(
     *                                                     property="user",
     *                                                     type="object",
     *                                                     ref="#/components/schemas/user_meta",
     *                                                 ),
     *                                             ),
     *                                          },
     *                                       ),
     *                                   },
     *                               ),
     *                          ),
     *                      )
     *                  }
     *              )
     *          ),
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     *
     *  @OA\Schema(
     *      schema="reviews_collection",
     *      description="Anime & Manga Reviews Resource",
     *
     *     @OA\Property(
     *          property="data",
     *          type="array",
     *
     *          @OA\Items(
     *              type="object",
     *              anyOf = {
     *                  @OA\Schema(ref="#/components/schemas/anime_review"),
     *                  @OA\Schema(ref="#/components/schemas/manga_review"),
     *              },
     *          ),
     *     ),
     *  ),
     */
    public function reviews(QueryTopReviewsCommand $request)
    {
        return $this->mediator->send($request);
    }
}
