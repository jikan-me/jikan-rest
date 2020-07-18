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
     *         @OA\JsonContent()
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
     *                   @OA\Items(
     *                      type="object",
     *
     *                      @OA\Property(
     *                          property="mal_id",
     *                          type="integer",
     *                          description="MyAnimeList ID"
     *                      ),
     *                      @OA\Property(
     *                          property="url",
     *                          type="string",
     *                          description="URL"
     *                      ),
     *                      @OA\Property(
     *                          property="title",
     *                          type="string",
     *                          description="Anime Title"
     *                      ),
     *                      @OA\Property(
     *                          property="images",
     *                          type="object",
     *                          description="Images",
     *                          @OA\Property(
     *                              property="jpg",
     *                              type="object",
     *                              description="Available images in JPG",
     *                              @OA\Property(
     *                                  property="image_url",
     *                                  type="string",
     *                                  description="Image URL JPG (225x335)",
     *                              ),
     *                              @OA\Property(
     *                                  property="small_image_url",
     *                                  type="string",
     *                                  description="Small Image URL JPG (50x74)",
     *                              ),
     *                              @OA\Property(
     *                                  property="large_image_url",
     *                                  type="string",
     *                                  description="Image URL JPG (300x446)",
     *                              ),
     *                          ),
     *                          @OA\Property(
     *                              property="webp",
     *                              type="object",
     *                              description="Available images in WEBP",
     *                              @OA\Property(
     *                                  property="image_url",
     *                                  type="string",
     *                                  description="Image URL WEBP (225x335)",
     *                              ),
     *                              @OA\Property(
     *                                  property="small_image_url",
     *                                  type="string",
     *                                  description="Small Image URL WEBP (50x74)",
     *                              ),
     *                              @OA\Property(
     *                                  property="large_image_url",
     *                                  type="string",
     *                                  description="Image URL WEBP (300x446)",
     *                              ),
     *                          ),
     *                      ),
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

            if (HttpHelper::hasError($response)) {
                return HttpResponse::notFound($request);
            }

            if ($results->isEmpty()) {
                $meta = [
                    'createdAt' => new UTCDateTime(),
                    'modifiedAt' => new UTCDateTime(),
                    'request_hash' => $this->fingerprint
                ];
            }
            $meta['modifiedAt'] = new UTCDateTime();

            $response = $meta + $response;

            if ($results->isEmpty()) {
                DB::table($this->getRouteTable($request))
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                DB::table($this->getRouteTable($request))
                    ->where('request_hash', $this->fingerprint)
                    ->update($response);
            }

            $results = DB::table($this->getRouteTable($request))
                ->where('request_hash', $this->fingerprint)
                ->get();
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
     *         @OA\JsonContent()
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

            if (HttpHelper::hasError($response)) {
                return HttpResponse::notFound($request);
            }

            if ($results->isEmpty()) {
                $meta = [
                    'createdAt' => new UTCDateTime(),
                    'modifiedAt' => new UTCDateTime(),
                    'request_hash' => $this->fingerprint
                ];
            }
            $meta['modifiedAt'] = new UTCDateTime();

            $response = $meta + $response;

            if ($results->isEmpty()) {
                DB::table($this->getRouteTable($request))
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                DB::table($this->getRouteTable($request))
                    ->where('request_hash', $this->fingerprint)
                    ->update($response);
            }

            $results = DB::table($this->getRouteTable($request))
                ->where('request_hash', $this->fingerprint)
                ->get();
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
     *         @OA\JsonContent()
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
     *              @OA\Property(
     *                   property="data",
     *                   type="array",
     *                   @OA\Items(
     *                      type="object",
     *
     *                      @OA\Property(
     *                          property="anime",
     *                          type="object",
     *                          description="Anime Meta",
     *
     *                          @OA\Property(
     *                              property="mal_id",
     *                              type="integer",
     *                              description="MyAnimeList ID"
     *                          ),
     *                          @OA\Property(
     *                              property="url",
     *                              type="string",
     *                              description="URL"
     *                          ),
     *                          @OA\Property(
     *                              property="title",
     *                              type="string",
     *                              description="Anime Title"
     *                          ),
     *                          @OA\Property(
     *                              property="images",
     *                              type="object",
     *                              description="Images",
     *                              @OA\Property(
     *                                  property="jpg",
     *                                  type="object",
     *                                  description="Available images in JPG",
     *                                  @OA\Property(
     *                                      property="image_url",
     *                                      type="string",
     *                                      description="Image URL JPG (225x335)",
     *                                  ),
     *                                  @OA\Property(
     *                                      property="small_image_url",
     *                                      type="string",
     *                                      description="Small Image URL JPG (50x74)",
     *                                  ),
     *                                  @OA\Property(
     *                                      property="large_image_url",
     *                                      type="string",
     *                                      description="Image URL JPG (300x446)",
     *                                  ),
     *                              ),
     *                              @OA\Property(
     *                                  property="webp",
     *                                  type="object",
     *                                  description="Available images in WEBP",
     *                                  @OA\Property(
     *                                      property="image_url",
     *                                      type="string",
     *                                      description="Image URL WEBP (225x335)",
     *                                  ),
     *                                  @OA\Property(
     *                                      property="small_image_url",
     *                                      type="string",
     *                                      description="Small Image URL WEBP (50x74)",
     *                                  ),
     *                                  @OA\Property(
     *                                      property="large_image_url",
     *                                      type="string",
     *                                      description="Image URL WEBP (300x446)",
     *                                  ),
     *                              ),
     *                          ),
     *                      ),
     *                      @OA\Property(
     *                          property="title",
     *                          type="string",
     *                          description="Promo Title"
     *                      ),
     *                      @OA\Property(
     *                          property="trailer",
     *                          type="array",
     *                          @OA\Items(
     *                              type="object",
     *                              ref="#/components/schemas/trailer",
     *                          ),
     *                      ),
     *                  ),
     *              ),
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

            if (HttpHelper::hasError($response)) {
                return HttpResponse::notFound($request);
            }

            if ($results->isEmpty()) {
                $meta = [
                    'createdAt' => new UTCDateTime(),
                    'modifiedAt' => new UTCDateTime(),
                    'request_hash' => $this->fingerprint
                ];
            }
            $meta['modifiedAt'] = new UTCDateTime();

            $response = $meta + $response;

            if ($results->isEmpty()) {
                DB::table($this->getRouteTable($request))
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                DB::table($this->getRouteTable($request))
                    ->where('request_hash', $this->fingerprint)
                    ->update($response);
            }

            $results = DB::table($this->getRouteTable($request))
                ->where('request_hash', $this->fingerprint)
                ->get();
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
     *         @OA\JsonContent()
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

            if (HttpHelper::hasError($response)) {
                return HttpResponse::notFound($request);
            }

            if ($results->isEmpty()) {
                $meta = [
                    'createdAt' => new UTCDateTime(),
                    'modifiedAt' => new UTCDateTime(),
                    'request_hash' => $this->fingerprint
                ];
            }
            $meta['modifiedAt'] = new UTCDateTime();

            $response = $meta + $response;

            if ($results->isEmpty()) {
                DB::table($this->getRouteTable($request))
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                DB::table($this->getRouteTable($request))
                    ->where('request_hash', $this->fingerprint)
                    ->update($response);
            }

            $results = DB::table($this->getRouteTable($request))
                ->where('request_hash', $this->fingerprint)
                ->get();
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
