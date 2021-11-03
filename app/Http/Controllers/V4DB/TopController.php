<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Character;
use App\Http\HttpHelper;
use App\Http\HttpResponse;
use App\Http\QueryBuilder\SearchQueryBuilderUsers;
use App\Http\QueryBuilder\TopQueryBuilderAnime;
use App\Http\QueryBuilder\TopQueryBuilderManga;
use App\Http\Resources\V4\AnimeCollection;
use App\Http\Resources\V4\CharacterCollection;
use App\Http\Resources\V4\MangaCollection;
use App\Http\Resources\V4\PersonCollection;
use App\Http\Resources\V4\ResultsResource;
use App\Manga;
use App\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\Helper\Constants;
use Jikan\Request\Reviews\RecentReviewsRequest;
use Jikan\Request\Top\TopPeopleRequest;
use MongoDB\BSON\UTCDateTime;

class TopController extends Controller
{
    const MAX_RESULTS_PER_PAGE = 25;

    /**
     *  @OA\Get(
     *     path="/top/anime",
     *     operationId="getTopAnime",
     *     tags={"top"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns top anime",
     *          @OA\JsonContent(
     *               ref="#/components/schemas/anime search"
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

        $results = TopQueryBuilderAnime::query(
            $request,
            Anime::query()
        );

        $results = $results
            ->paginate(
                $limit,
                ['*'],
                null,
                $page
            );

        return new AnimeCollection(
            $results
        );
    }

    /**
     *  @OA\Get(
     *     path="/top/manga",
     *     operationId="getTopManga",
     *     tags={"top"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns top manga",
     *          @OA\JsonContent(
     *               ref="#/components/schemas/manga search"
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

        $results = TopQueryBuilderManga::query(
            $request,
            Manga::query()
        );

        $results = $results
            ->paginate(
                $limit,
                ['*'],
                null,
                $page
            );

        return new MangaCollection(
            $results
        );
    }

    /**
     *  @OA\Get(
     *     path="/top/people",
     *     operationId="getTopPeople",
     *     tags={"top"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns top people",
     *          @OA\JsonContent(
     *               ref="#/components/schemas/people search"
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

        $results = Person::query()
            ->whereNotNull('member_favorites')
            ->where('member_favorites', '>', 0)
            ->orderBy('member_favorites', 'desc');

        $results = $results
            ->paginate(
                $limit,
                ['*'],
                null,
                $page
            );

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
     *     @OA\Response(
     *         response="200",
     *         description="Returns top characters",
     *          @OA\JsonContent(
     *               ref="#/components/schemas/characters search"
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

        $results = Character::query()
            ->whereNotNull('member_favorites')
            ->where('member_favorites', '>', 0)
            ->orderBy('member_favorites', 'desc');

        $results = $results
            ->paginate(
                $limit,
                ['*'],
                null,
                $page
            );

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
     *                                              @OA\Schema(ref="#/components/schemas/anime review"),
     *                                              @OA\Schema(
     *                                                 @OA\Property(
     *                                                     property="anime",
     *                                                     type="object",
     *                                                     ref="#/components/schemas/anime meta",
     *                                                 ),
     *                                             ),
     *                                              @OA\Schema(
     *                                                 @OA\Property(
     *                                                     property="user",
     *                                                     type="object",
     *                                                     ref="#/components/schemas/user meta",
     *                                                 ),
     *                                             ),
     *                                          },
     *                                       ),
     *                                       @OA\Schema(
     *                                          allOf={
     *                                              @OA\Schema(ref="#/components/schemas/manga review"),
     *                                              @OA\Schema(
     *                                                 @OA\Property(
     *                                                     property="manga",
     *                                                     type="object",
     *                                                     ref="#/components/schemas/manga meta",
     *                                                 ),
     *                                             ),
     *                                              @OA\Schema(
     *                                                 @OA\Property(
     *                                                     property="user",
     *                                                     type="object",
     *                                                     ref="#/components/schemas/user meta",
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
     *      schema="reviews collection",
     *      description="Anime & Manga Reviews Resource",
     *
     *     @OA\Property(
     *          property="data",
     *          type="array",
     *
     *          @OA\Items(
     *              type="object",
     *              anyOf = {
     *                  @OA\Schema(ref="#/components/schemas/anime review"),
     *                  @OA\Schema(ref="#/components/schemas/manga review"),
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
