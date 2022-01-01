<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Http\HttpHelper;
use App\Http\HttpResponse;
use App\Http\Resources\V4\AnimeCharactersResource;
use App\Http\Resources\V4\AnimeForumResource;
use App\Http\Resources\V4\MangaRelationsResource;
use App\Http\Resources\V4\ResultsResource;
use App\Http\Resources\V4\ReviewsResource;
use App\Http\Resources\V4\UserUpdatesResource;
use App\Http\Resources\V4\RecommendationsResource;
use App\Http\Resources\V4\MoreInfoResource;
use App\Http\Resources\V4\AnimeNewsResource;
use App\Http\Resources\V4\AnimeStatisticsResource;
use App\Http\Resources\V4\ForumResource;
use App\Http\Resources\V4\MangaCharactersResource;
use App\Http\Resources\V4\MangaStatisticsResource;
use App\Http\Resources\V4\NewsResource;
use App\Http\Resources\V4\PicturesResource;
use App\Manga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\Request\Anime\AnimeCharactersAndStaffRequest;
use Jikan\Request\Anime\AnimeForumRequest;
use Jikan\Request\Anime\AnimeMoreInfoRequest;
use Jikan\Request\Anime\AnimeNewsRequest;
use Jikan\Request\Anime\AnimePicturesRequest;
use Jikan\Request\Anime\AnimeRecentlyUpdatedByUsersRequest;
use Jikan\Request\Anime\AnimeRecommendationsRequest;
use Jikan\Request\Anime\AnimeReviewsRequest;
use Jikan\Request\Anime\AnimeStatsRequest;
use Jikan\Request\Manga\MangaCharactersRequest;
use Jikan\Request\Manga\MangaForumRequest;
use Jikan\Request\Manga\MangaMoreInfoRequest;
use Jikan\Request\Manga\MangaNewsRequest;
use Jikan\Request\Manga\MangaPicturesRequest;
use Jikan\Request\Manga\MangaRecentlyUpdatedByUsersRequest;
use Jikan\Request\Manga\MangaRecommendationsRequest;
use Jikan\Request\Manga\MangaRequest;
use Jikan\Request\Manga\MangaReviewsRequest;
use Jikan\Request\Manga\MangaStatsRequest;
use MongoDB\BSON\UTCDateTime;
use mysql_xdevapi\Result;

class MangaController extends Controller
{
    /**
     *  @OA\Get(
     *     path="/manga/{id}",
     *     operationId="getMangaById",
     *     tags={"manga"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns pictures related to the entry",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/manga"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function main(Request $request, int $id)
    {
        $results = Manga::query()
            ->where('mal_id', $id)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $response = Manga::scrape($id);

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
                Manga::query()
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                Manga::query()
                    ->where('mal_id', $id)
                    ->update($response);
            }

            $results = Manga::query()
                ->where('mal_id', $id)
                ->get();
        }


        if ($results->isEmpty()) {
            return HttpResponse::notFound($request);
        }

        $response = (new \App\Http\Resources\V4\MangaResource(
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
     *     path="/manga/{id}/characters",
     *     operationId="getMangaCharacters",
     *     tags={"manga"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns manga characters resource",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/manga characters"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function characters(Request $request, int $id)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $manga = ['characters' => $this->jikan->getMangaCharacters(new MangaCharactersRequest($id))];
            $response = \json_decode($this->serializer->serialize($manga, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new MangaCharactersResource(
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
     *     path="/manga/{id}/news",
     *     operationId="getMangaNews",
     *     tags={"manga"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of manga news topics",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/manga news"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     *
     *  @OA\Schema(
     *      schema="manga news",
     *      description="Manga News Resource",
     *
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/pagination"),
     *          @OA\Schema(ref="#/components/schemas/news"),
     *      }
     *  )
     */
    public function news(Request $request, int $id)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $page = $request->get('page') ?? 1;
            $manga = $this->jikan->getNewsList(new MangaNewsRequest($id, $page));
            $response = \json_decode($this->serializer->serialize($manga, 'json'), true);

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
     *     path="/manga/{id}/forum",
     *     operationId="getMangaTopics",
     *     tags={"manga"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of manga forum topics",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/forum"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function forum(Request $request, int $id)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $topic = $request->get('topic');
            $manga = ['topics' => $this->jikan->getMangaForum(new MangaForumRequest($id))];
            $response = \json_decode($this->serializer->serialize($manga, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new ForumResource(
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
     *     path="/manga/{id}/pictures",
     *     operationId="getMangaPictures",
     *     tags={"manga"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of manga forum topics",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/pictures"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     * @OA\Schema(
     *     schema="manga pictures",
     *     description="Manga Pictures",
     *     @OA\Property(
     *         property="data",
     *         type="array",
     * 
     *         @OA\Items(
     *             @OA\Property(
     *                 property="image_url",
     *                 type="string",
     *                 description="Default JPG Image Size URL"
     *             ),
     *             @OA\Property(
     *                 property="large_image_url",
     *                 type="string",
     *                 description="Large JPG Image Size URL"
     *             ),
     *         )
     *     )
     * )
     */
    public function pictures(Request $request, int $id)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $manga = ['pictures' => $this->jikan->getMangaPictures(new MangaPicturesRequest($id))];
            $response = \json_decode($this->serializer->serialize($manga, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new PicturesResource(
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
     *     path="/manga/{id}/statistics",
     *     operationId="getMangaStatistics",
     *     tags={"manga"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns anime statistics",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/manga statistics"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function stats(Request $request, int $id)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $manga = $this->jikan->getMangaStats(new MangaStatsRequest($id));
            $response = \json_decode($this->serializer->serialize($manga, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new MangaStatisticsResource(
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
     *     path="/manga/{id}/moreinfo",
     *     operationId="getMangaMoreInfo",
     *     tags={"manga"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns manga moreinfo",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/moreinfo"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function moreInfo(Request $request, int $id)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $manga = ['moreinfo' => $this->jikan->getMangaMoreInfo(new MangaMoreInfoRequest($id))];
            $response = \json_decode($this->serializer->serialize($manga, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new MoreInfoResource(
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
     *     path="/manga/{id}/recommendations",
     *     operationId="getMangaRecommendations",
     *     tags={"manga"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns manga recommendations",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/entry recommendations"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function recommendations(Request $request, int $id)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $manga = ['recommendations' => $this->jikan->getMangaRecommendations(new MangaRecommendationsRequest($id))];
            $response = \json_decode($this->serializer->serialize($manga, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new RecommendationsResource(
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
     *     path="/manga/{id}/userupdates",
     *     operationId="getMangaUserUpdates",
     *     tags={"manga"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns manga user updates",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/manga userupdates"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function userupdates(Request $request, int $id)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $page = $request->get('page') ?? 1;
            $manga = $this->jikan->getMangaRecentlyUpdatedByUsers(new MangaRecentlyUpdatedByUsersRequest($id, $page));
            $response = \json_decode($this->serializer->serialize($manga, 'json'), true);

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
     *     path="/manga/{id}/reviews",
     *     operationId="getMangaReviews",
     *     tags={"manga"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns manga reviews",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/manga reviews"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function reviews(Request $request, int $id)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $page = $request->get('page') ?? 1;
            $manga = $this->jikan->getMangaReviews(new MangaReviewsRequest($id, $page));
            $response = \json_decode($this->serializer->serialize($manga, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new ReviewsResource(
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
     *     path="/manga/{id}/relations",
     *     operationId="getMangaRelations",
     *     tags={"manga"},
     *
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns manga relations",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/relation"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function relations(Request $request, int $id)
    {
        $results = Manga::query()
            ->where('mal_id', $id)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $response = Manga::scrape($id);

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
                Manga::query()
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                Manga::query()
                    ->where('mal_id', $id)
                    ->update($response);
            }

            $results = Manga::query()
                ->where('mal_id', $id)
                ->get();
        }

        if ($results->isEmpty()) {
            return HttpResponse::notFound($request);
        }


        $response = (new MangaRelationsResource(
            $results->first()
        ))->response();

        return $this->prepareResponse(
            $response,
            $results,
            $request
        );
    }
}
