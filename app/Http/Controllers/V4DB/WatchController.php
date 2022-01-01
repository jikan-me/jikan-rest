<?php

namespace App\Http\Controllers\V4DB;

use App\Http\HttpHelper;
use App\Http\HttpResponse;
use App\Http\Resources\V4\ResultsResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\Helper\Constants;
use Jikan\Request\Anime\AnimeNewsRequest;
use Jikan\Request\Watch\PopularEpisodesRequest;
use Jikan\Request\Watch\PopularPromotionalVideosRequest;
use Jikan\Request\Watch\RecentEpisodesRequest;
use Jikan\Request\Watch\RecentPromotionalVideosRequest;
use MongoDB\BSON\UTCDateTime;

class WatchController extends Controller
{

    /**
     *  @OA\Get(
     *     path="/watch/episodes",
     *     operationId="getWatchRecentEpisodes",
     *     tags={"watch"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns Recently Added Episodes",
     *          @OA\JsonContent(
     *               ref="#/components/schemas/watch episodes"
     *          )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     *
     *  @OA\Schema(
     *      schema="watch episodes",
     *      description="Watch Episodes",
     *
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/pagination"),
     *          @OA\Schema(
     *              @OA\Property(
     *                   property="data",
     *                   type="array",
     *
     *                   @OA\Items(
     *                      type="object",
     *
     *                       @OA\Property(
     *                           property="entry",
     *                           type="object",
     *                           ref="#/components/schemas/anime meta"
     *                       ),
     *                      @OA\Property(
     *                          property="episodes",
     *                          type="array",
     *                          description="Recent Episodes (max 2 listed)",
     *                          @OA\Items(
     *                              type="object",
     *                              @OA\Property(
     *                                  property="mal_id",
     *                                  type="string",
     *                                  description="MyAnimeList ID",
     *                              ),
     *                              @OA\Property(
     *                                  property="url",
     *                                  type="string",
     *                                  description="MyAnimeList URL",
     *                              ),
     *                              @OA\Property(
     *                                  property="title",
     *                                  type="string",
     *                                  description="Episode Title",
     *                              ),
     *                              @OA\Property(
     *                                  property="premium",
     *                                  type="boolean",
     *                                  description="For MyAnimeList Premium Users",
     *                              ),
     *                          ),
     *                      ),
     *                      @OA\Property(
     *                          property="region_locked",
     *                          type="boolean",
     *                          description="Region Locked Episode"
     *                      ),
     *                  ),
     *              ),
     *          ),
     *     },
     *  ),
     */
    public function recentEpisodes(Request $request)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $items = $this->jikan->getRecentEpisodes(new RecentEpisodesRequest());
            $response = \json_decode($this->serializer->serialize($items, 'json'), true);

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

    /**
     *  @OA\Get(
     *     path="/watch/episodes/popular",
     *     operationId="getWatchPopularEpisodes",
     *     tags={"watch"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns Popular Episodes",
     *          @OA\JsonContent(
     *               ref="#/components/schemas/watch episodes"
     *          )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function popularEpisodes(Request $request)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $items = $this->jikan->getPopularEpisodes(new PopularEpisodesRequest());
            $response = \json_decode($this->serializer->serialize($items, 'json'), true);

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

    /**
     *  @OA\Get(
     *     path="/watch/promos",
     *     operationId="getWatchRecentPromos",
     *     tags={"watch"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns Recently Added Promotional Videos",
     *          @OA\JsonContent(
     *               ref="#/components/schemas/watch promos"
     *          )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     *
     *  @OA\Schema(
     *      schema="watch promos",
     *      description="Watch Promos",
     *
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/pagination"),
     *          @OA\Schema(
     *
     *              allOf={
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="title",
     *                          type="string",
     *                          description="Promo Title"
     *                      ),
     *                  ),
     *                  @OA\Schema (
     *                      @OA\Property(
     *                           property="data",
     *                           type="array",
     *
     *                           @OA\Items(
     *                              type="object",
     *                               @OA\Property(
     *                                   property="entry",
     *                                   type="object",
     *                                   ref="#/components/schemas/anime meta"
     *                               ),
     *                              @OA\Property(
     *                                  property="trailer",
     *                                  type="array",
     *                                  @OA\Items(
     *                                      type="object",
     *                                      ref="#/components/schemas/trailer",
     *                                  ),
     *                              ),
     *                          ),
     *                      ),
     *                  ),
     *              },
     *          ),
     *     },
     *  ),
     */
    public function recentPromos(Request $request)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $page = $request->get('page') ?? 1;
            $items = $this->jikan->getRecentPromotionalVideos(new RecentPromotionalVideosRequest($page));
            $response = \json_decode($this->serializer->serialize($items, 'json'), true);

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

    /**
     *  @OA\Get(
     *     path="/watch/promos/popular",
     *     operationId="getWatchPopularPromos",
     *     tags={"watch"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns Popular Promotional Videos",
     *          @OA\JsonContent(
     *               ref="#/components/schemas/watch promos"
     *          )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function popularPromos(Request $request)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $items = $this->jikan->getPopularPromotionalVideos(new PopularPromotionalVideosRequest());
            $response = \json_decode($this->serializer->serialize($items, 'json'), true);

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
