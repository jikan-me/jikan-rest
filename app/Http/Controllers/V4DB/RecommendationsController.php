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
     *  @OA\Get(
     *     path="/recommendations/anime",
     *     operationId="getRecentAnimeRecommendations",
     *     tags={"recommendations"},
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
     *     operationId="getRecentMangaRecommendations",
     *     tags={"recommendations"},
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
