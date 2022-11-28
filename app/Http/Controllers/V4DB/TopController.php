<?php

namespace App\Http\Controllers\V4DB;

use App\Http\Resources\V4\AnimeCollection;
use App\Http\Resources\V4\CharacterCollection;
use App\Http\Resources\V4\MangaCollection;
use App\Http\Resources\V4\PersonCollection;
use App\Http\Resources\V4\ResultsResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\Helper\Constants;
use Jikan\Request\Reviews\RecentReviewsRequest;


class TopController extends ControllerWithQueryBuilderProvider
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
     *          @OA\Schema(type="string",enum={"airing", "upcoming", "bypopularity", "favorite"})
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
    public function anime(Request $request)
    {
        return $this->preparePaginatedResponse(AnimeCollection::class, "top_anime", $request);
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
     *          @OA\Schema(type="string",enum={"publishing", "upcoming", "bypopularity", "favorite"})
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
    public function manga(Request $request)
    {
        return $this->preparePaginatedResponse(MangaCollection::class, "top_manga", $request);
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
    public function people(Request $request)
    {
        $results = $this->getQueryBuilder("people", $request)
            ->whereNotNull('member_favorites')
            ->where('member_favorites', '>', 0)
            ->orderBy('member_favorites', 'desc');

        $results = $this->getPaginator("people", $request, $results);

        return new PersonCollection(
            $results
        );
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
    public function characters(Request $request)
    {
        $results = $this->getQueryBuilder("character", $request)
            ->whereNotNull('member_favorites')
            ->where('member_favorites', '>', 0)
            ->orderBy('member_favorites', 'desc');

        $results = $this->getPaginator("character", $request, $results);

        return new CharacterCollection(
            $results
        );
    }

    /**
     *  @OA\Get(
     *     path="/top/reviews",
     *     operationId="getTopReviews",
     *     tags={"top"},
     *
     *     @OA\Parameter(ref="#/components/parameters/page"),
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
    public function reviews(Request $request)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $page = $request->get('page') ?? 1;
            $data = $this->jikan->getRecentReviews(
                new RecentReviewsRequest(Constants::RECENT_REVIEW_BEST_VOTED, $page)
            );
            $response = \json_decode($this->serializer->serialize($data, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new ResultsResource(
            $results->first()
        ))->response();

        return $this->prepareResponse(
            $response,
            $results,
            $request
        );
    }
}
