<?php

namespace App\Http\Controllers\V4DB;

use App\Http\HttpHelper;
use App\Http\HttpResponse;
use App\Http\Resources\V4\ResultsResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\Helper\Constants;
use Jikan\Request\Reviews\RecentReviewsRequest;
use Jikan\Request\Reviews\ReviewsRequest;
use MongoDB\BSON\UTCDateTime;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

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
            $sort = $request->get('sort') ?? Constants::REVIEWS_SORT_MOST_VOTED;

            if (!in_array($sort, [Constants::REVIEWS_SORT_MOST_VOTED, Constants::REVIEWS_SORT_NEWEST, Constants::REVIEWS_SORT_OLDEST])) {
                throw new BadRequestException('Invalid sort for reviews. Please refer to the documentation: https://docs.api.jikan.moe/');
            }

            $spoilers = $request->get('spoilers') ?? false;
            $preliminary = $request->get('preliminary') ?? false;

            $anime = $this->jikan
                ->getReviews(
                    new ReviewsRequest(
                        Constants::ANIME,
                        $page,
                        $sort,
                        $spoilers,
                        $preliminary
                    )
                );

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
            $sort = $request->get('sort') ?? Constants::REVIEWS_SORT_MOST_VOTED;

            if (!in_array($sort, [Constants::REVIEWS_SORT_MOST_VOTED, Constants::REVIEWS_SORT_NEWEST, Constants::REVIEWS_SORT_OLDEST])) {
                throw new BadRequestException('Invalid sort for reviews. Please refer to the documentation: https://docs.api.jikan.moe/');
            }

            $spoilers = $request->get('spoilers') ?? false;
            $preliminary = $request->get('preliminary') ?? false;

            $anime = $this->jikan
                ->getReviews(
                    new ReviewsRequest(
                        Constants::MANGA,
                        $page,
                        $sort,
                        $spoilers,
                        $preliminary
                    )
                );

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
