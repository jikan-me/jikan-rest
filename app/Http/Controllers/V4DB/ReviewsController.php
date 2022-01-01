<?php

namespace App\Http\Controllers\V4DB;

use App\Http\HttpHelper;
use App\Http\HttpResponse;
use App\Http\Resources\V4\ResultsResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\Helper\Constants;
use Jikan\Request\Reviews\RecentReviewsRequest;
use MongoDB\BSON\UTCDateTime;

class ReviewsController extends Controller
{
    /**
     *  @OA\Get(
     *     path="/reviews/anime",
     *     operationId="getRecentAnimeReviews",
     *     tags={"reviews"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns recent anime reviews",
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
     *                                   allOf={
     *                                       @OA\Schema(ref="#/components/schemas/anime review"),
     *                                       @OA\Schema(
     *                                          @OA\Property(
     *                                              property="anime",
     *                                              type="object",
     *                                              ref="#/components/schemas/anime meta",
     *                                          ),
     *                                      ),
     *                                       @OA\Schema(
     *                                          @OA\Property(
     *                                              property="user",
     *                                              type="object",
     *                                              ref="#/components/schemas/user meta",
     *                                          ),
     *                                      ),
     *                                   }
     *                               )
     *                          ),
     *                      )
     *                  }
     *              )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
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
            $anime = $this->jikan->getRecentReviews(new RecentReviewsRequest(Constants::RECENT_REVIEW_ANIME, $page));
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
     *     path="/reviews/manga",
     *     operationId="getRecentMangaReviews",
     *     tags={"reviews"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns recent manga reviews",
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
     *                                   allOf={
     *                                       @OA\Schema(ref="#/components/schemas/manga review"),
     *                                       @OA\Schema(
     *                                          @OA\Property(
     *                                              property="manga",
     *                                              type="object",
     *                                              ref="#/components/schemas/manga meta",
     *                                          ),
     *                                      ),
     *                                       @OA\Schema(
     *                                          @OA\Property(
     *                                              property="user",
     *                                              type="object",
     *                                              ref="#/components/schemas/user meta",
     *                                          ),
     *                                      ),
     *                                   }
     *                               )
     *                          ),
     *                      )
     *                  }
     *              )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
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
            $anime = $this->jikan->getRecentReviews(new RecentReviewsRequest(Constants::RECENT_REVIEW_MANGA, $page));
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
