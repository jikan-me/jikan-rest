<?php

namespace App\Http\Controllers\V4DB;

use App\Http\HttpHelper;
use App\Http\HttpResponse;
use App\Http\Resources\V4\ResultsResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\Helper\Constants;
use Jikan\Request\Recommendations\RecentRecommendationsRequest;
use Jikan\Request\Reviews\RecentReviewsRequest;
use MongoDB\BSON\UTCDateTime;

class RecommendationsController extends Controller
{

    /**
     *  @OA\Schema(
     *      schema="recent recommendations",
     *      description="Recent Recommendations",
     *
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/pagination"),
     *          @OA\Schema(
     *              @OA\Property(
     *                   property="data",
     *                   type="array",
     *                   @OA\Items(
     *                      type="object",
     *                      anyOf={
     *                          @OA\Schema(ref="#/components/schemas/anime recommendation"),
     *                          @OA\Schema(ref="#/components/schemas/manga recommendation"),
     *                      },
     *                  ),
     *              ),
     *          ),
     *     }
     *  ),
     */

    /**
     *  @OA\Get(
     *     path="/recommendations/anime",
     *     operationId="getAnimeRecommendations",
     *     tags={"recommendations"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns recent anime recommendations",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/recent recommendations"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     *
     * @OA\Schema(
     *     schema="anime recommendation",
     *     description="Anime Recommendations",
     *     @OA\Property(
     *          property="anime",
     *          type="array",
     *          description="Similar Anime",
     *          @OA\Items(
     *               type="object",
     *               ref="#/components/schemas/mal_url",
     *          ),
     *     ),
     * ),
     */
    public function anime(Request $request)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $page = $request->get('page') ?? 1;
            $anime = $this->jikan->getRecentRecommendations(new RecentRecommendationsRequest(Constants::RECENT_RECOMMENDATION_ANIME, $page));
            $response = \json_decode($this->serializer->serialize($anime, 'json'), true);

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
     *     path="/recommendations/manga",
     *     operationId="getMangaRecommendations",
     *     tags={"recommendations"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns recent manga recommendations",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/recent recommendations"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     *
     * @OA\Schema(
     *     schema="manga recommendation",
     *     description="Manga Recommendations",
     *     @OA\Property(
     *          property="manga",
     *          type="array",
     *          description="Similar Manga",
     *          @OA\Items(
     *               type="object",
     *               ref="#/components/schemas/mal_url",
     *          ),
     *     ),
     * ),
     */
    public function manga(Request $request)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $page = $request->get('page') ?? 1;
            $anime = $this->jikan->getRecentRecommendations(new RecentRecommendationsRequest(Constants::RECENT_RECOMMENDATION_MANGA, $page));
            $response = \json_decode($this->serializer->serialize($anime, 'json'), true);

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
