<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Http\HttpHelper;
use App\Http\HttpResponse;
use App\Http\QueryBuilder\UserListQueryBuilder;
use App\Http\Resources\V4\AnimeCharactersResource;
use App\Http\Resources\V4\CommonResource;
use App\Http\Resources\V4\ProfileFriendsResource;
use App\Http\Resources\V4\ProfileHistoryResource;
use App\Http\Resources\V4\ResultsResource;
use App\Http\Resources\V4\UserProfileAnimeListCollection;
use App\Http\Resources\V4\UserProfileAnimeListResource;
use App\Http\Resources\V4\UserProfileMangaListCollection;
use App\Http\Resources\V4\UserProfileMangaListResource;
use App\Profile;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\Request\Anime\AnimeCharactersAndStaffRequest;
use Jikan\Request\User\RecentlyOnlineUsersRequest;
use Jikan\Request\User\UserAnimeListRequest;
use Jikan\Request\User\UserClubsRequest;
use Jikan\Request\User\UserMangaListRequest;
use Jikan\Request\User\UserProfileRequest;
use Jikan\Request\User\UserFriendsRequest;
use Jikan\Request\User\UserHistoryRequest;
use Jikan\Request\User\UserRecommendationsRequest;
use Jikan\Request\User\UserReviewsRequest;
use MongoDB\BSON\UTCDateTime;

class UserController extends Controller
{

    /**
     *  @OA\Get(
     *     path="/users/{username}",
     *     operationId="getUserProfile",
     *     tags={"users"},
     *
     *     @OA\Parameter(
     *       name="username",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns user profile",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/user profile"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function profile(Request $request, string $username)
    {
        $username = strtolower($username);

        $results = Profile::query()
            ->where('internal_username', $username)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $response = Profile::scrape($username);

            if ($results->isEmpty()) {
                $meta = [
                    'createdAt' => new UTCDateTime(),
                    'modifiedAt' => new UTCDateTime(),
                    'request_hash' => $this->fingerprint,
                    'internal_username' => $username
                ];
            }
            $meta['modifiedAt'] = new UTCDateTime();

            $response = $meta + $response;

            if ($results->isEmpty()) {
                Profile::query()
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                Profile::query()
                    ->where('internal_username', $username)
                    ->update($response);
            }

            $results = Profile::query()
                ->where('internal_username', $username)
                ->get();
        }

        if ($results->isEmpty()) {
            return HttpResponse::notFound($request);
        }

        $response = (new \App\Http\Resources\V4\ProfileResource(
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
     *     path="/users/{username}/statistics",
     *     operationId="getUserStatistics",
     *     tags={"users"},
     *
     *     @OA\Parameter(
     *       name="username",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns user statistics",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/user statistics"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function statistics(Request $request, string $username)
    {

        $username = strtolower($username);

        $results = Profile::query()
            ->where('internal_username', $username)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $response = Profile::scrape($username);

            if ($results->isEmpty()) {
                $meta = [
                    'createdAt' => new UTCDateTime(),
                    'modifiedAt' => new UTCDateTime(),
                    'request_hash' => $this->fingerprint,
                    'internal_username' => $username
                ];
            }
            $meta['modifiedAt'] = new UTCDateTime();

            $response = $meta + $response;

            if ($results->isEmpty()) {
                Profile::query()
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                Profile::query()
                    ->where('internal_username', $username)
                    ->update($response);
            }

            $results = Profile::query()
                ->where('internal_username', $username)
                ->get();
        }

        if ($results->isEmpty()) {
            return HttpResponse::notFound($request);
        }

        $response = (new \App\Http\Resources\V4\ProfileStatisticsResource(
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
     *     path="/users/{username}/favorites",
     *     operationId="getUserFavorites",
     *     tags={"users"},
     *
     *     @OA\Parameter(
     *       name="username",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns user favorites",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/user favorites"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function favorites(Request $request, string $username)
    {

        $username = strtolower($username);

        $results = Profile::query()
            ->where('internal_username', $username)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $response = Profile::scrape($username);

            if ($results->isEmpty()) {
                $meta = [
                    'createdAt' => new UTCDateTime(),
                    'modifiedAt' => new UTCDateTime(),
                    'request_hash' => $this->fingerprint,
                    'internal_username' => $username
                ];
            }
            $meta['modifiedAt'] = new UTCDateTime();

            $response = $meta + $response;

            if ($results->isEmpty()) {
                Profile::query()
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                Profile::query()
                    ->where('internal_username', $username)
                    ->update($response);
            }

            $results = Profile::query()
                ->where('internal_username', $username)
                ->get();
        }

        if ($results->isEmpty()) {
            return HttpResponse::notFound($request);
        }

        $response = (new \App\Http\Resources\V4\ProfileFavoritesResource(
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
     *     path="/users/{username}/userupdates",
     *     operationId="getUserUpdates",
     *     tags={"users"},
     *
     *     @OA\Parameter(
     *       name="username",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns user updates",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/user updates"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function userupdates(Request $request, string $username)
    {

        $username = strtolower($username);

        $results = Profile::query()
            ->where('internal_username', $username)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $response = Profile::scrape($username);

            if ($results->isEmpty()) {
                $meta = [
                    'createdAt' => new UTCDateTime(),
                    'modifiedAt' => new UTCDateTime(),
                    'request_hash' => $this->fingerprint,
                    'internal_username' => $username
                ];
            }
            $meta['modifiedAt'] = new UTCDateTime();

            $response = $meta + $response;

            if ($results->isEmpty()) {
                Profile::query()
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                Profile::query()
                    ->where('internal_username', $username)
                    ->update($response);
            }

            $results = Profile::query()
                ->where('internal_username', $username)
                ->get();
        }

        if ($results->isEmpty()) {
            return HttpResponse::notFound($request);
        }

        $response = (new \App\Http\Resources\V4\ProfileLastUpdatesResource(
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
     *     path="/users/{username}/about",
     *     operationId="getUserAbout",
     *     tags={"users"},
     *
     *     @OA\Parameter(
     *       name="username",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns user about in raw HTML",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/user about"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function about(Request $request, string $username)
    {

        $username = strtolower($username);

        $results = Profile::query()
            ->where('internal_username', $username)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $response = Profile::scrape($username);

            if ($results->isEmpty()) {
                $meta = [
                    'createdAt' => new UTCDateTime(),
                    'modifiedAt' => new UTCDateTime(),
                    'request_hash' => $this->fingerprint,
                    'internal_username' => $username
                ];
            }
            $meta['modifiedAt'] = new UTCDateTime();

            $response = $meta + $response;

            if ($results->isEmpty()) {
                Profile::query()
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                Profile::query()
                    ->where('internal_username', $username)
                    ->update($response);
            }

            $results = Profile::query()
                ->where('internal_username', $username)
                ->get();
        }

        if ($results->isEmpty()) {
            return HttpResponse::notFound($request);
        }

        $response = (new \App\Http\Resources\V4\ProfileAboutResource(
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
     *     path="/users/{username}/history/{type}",
     *     operationId="getUserHistory",
     *     tags={"users"},
     *
     *     @OA\Parameter(
     *       name="username",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *       name="type",
     *       in="path",
     *       required=false,
     *       @OA\Schema(type="string",enum={"anime", "manga"})
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns user history (past 30 days)",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/user history"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function history(Request $request, string $username, ?string $type = null)
    {
        if (!is_null($type)) {
            $type = strtolower($type);
        }

        if (!is_null($type) && !\in_array($type, ['anime', 'manga'])) {
            return HttpResponse::badRequest($request);
        }

        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $data = ['history'=>$this->jikan->getUserHistory(new UserHistoryRequest($username, $type))];
            $response = \json_decode($this->serializer->serialize($data, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new ProfileHistoryResource(
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
     *     path="/users/{username}/friends",
     *     operationId="getUserFriends",
     *     tags={"users"},
     *
     *     @OA\Parameter(
     *       name="username",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns user friends",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/user friends"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     *
     *  @OA\Schema(
     *      schema="user friends",
     *      description="User Friends",
     *
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/pagination"),
     *          @OA\Schema(
     *
     *              @OA\Property(
     *                   property="data",
     *                   type="array",
     *
     *                   @OA\Items(
     *                      type="object",
     *
     *                      allOf={
     *                          @OA\Schema(
     *
     *                              @OA\Property(
     *                                  property="user",
     *                                  type="object",
     *                                  ref="#/components/schemas/user meta"
     *                              ),
     *                          ),
     *                          @OA\Schema (
     *                              @OA\Property(
     *                                  property="last_online",
     *                                  type="string",
     *                                  description="Last Online Date ISO8601 format"
     *                              ),
     *                              @OA\Property(
     *                                  property="friends_since",
     *                                  type="string",
     *                                  description="Friends Since Date ISO8601 format"
     *                              ),
     *                          ),
     *                      },
     *                  ),
     *              ),
     *          ),
     *     }
     *  ),
     */
    public function friends(Request $request, string $username)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $page = $request->get('page') ?? 1;
            $data = $this->jikan->getUserFriends(new UserFriendsRequest($username, $page));
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

    /**
     *  @OA\Get(
     *     path="/users/{username}/animelist",
     *     operationId="getUserAnimelist",
     *     tags={"users"},
     *
     *     @OA\Parameter(
     *       name="username",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns user anime list",
     *         @OA\JsonContent(
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     *
     */
    public function animelist(Request $request, string $username, ?string $status = null)
    {
        if (!is_null($status)) {
            $status = strtolower($status);

            if (!\in_array($status, ['all', 'watching', 'completed', 'onhold', 'dropped', 'plantowatch'])) {
                return HttpResponse::badRequest($request);
            }
        }
        $status = $this->listStatusToId($status);


        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $page = $request->get('page') ?? 1;
            $data = $this->jikan->getUserAnimeList(
                UserListQueryBuilder::create(
                    $request,
                    new UserAnimeListRequest($username, $page, $status)
                )
            );
            $response = ['anime' => \json_decode($this->serializer->serialize($data, 'json'), true)];

            $results = $this->updateCache($request, $results, $response);
        }

        $listResults = $results->first()['anime'];

        foreach ($listResults as &$result) {
            $result = (new UserProfileAnimeListResource($result));
        }

        $response = (new UserProfileAnimeListCollection(
            $listResults
        ))->response($request);

        return $this->prepareResponse(
            $response,
            $results,
            $request
        );
    }

    /**
     *  @OA\Get(
     *     path="/users/{username}/mangalist",
     *     operationId="getUserMangaList",
     *     tags={"users"},
     *
     *     @OA\Parameter(
     *       name="username",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns user manga list",
     *         @OA\JsonContent(
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     *
     */
    public function mangalist(Request $request, string $username, ?string $status = null)
    {
        if (!is_null($status)) {
            $status = strtolower($status);

            if (!\in_array($status, ['all', 'reading', 'completed', 'onhold', 'dropped', 'plantoread', 'ptr'])) {
                return response()->json([
                    'error' => 'Bad Request'
                ])->setStatusCode(400);
            }
        }
        $status = $this->listStatusToId($status);

        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $page = $request->get('page') ?? 1;
            $data = $this->jikan->getUserMangaList(
                UserListQueryBuilder::create(
                    $request,
                    new UserMangaListRequest($username, $page, $status)
                )
            );
            $response = ['manga' => \json_decode($this->serializer->serialize($data, 'json'), true)];

            $results = $this->updateCache($request, $results, $response);
        }

        $listResults = $results->first()['manga'];

        foreach ($listResults as &$result) {
            $result = (new UserProfileMangaListResource($result));
        }

        $response = (new UserProfileMangaListCollection(
            $listResults
        ))->response($request);

        return $this->prepareResponse(
            $response,
            $results,
            $request
        );
    }

    /**
     *  @OA\Get(
     *     path="/users/{username}/reviews",
     *     operationId="getUserReviews",
     *     tags={"users"},
     *
     *     @OA\Parameter(
     *       name="username",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns user reviews",
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
     */
    public function reviews(Request $request, string $username)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $page = $request->get('page') ?? 1;
            $data = $this->jikan->getUserReviews(new UserReviewsRequest($username, $page));
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

    /**
     *  @OA\Get(
     *     path="/users/{username}/recommendations",
     *     operationId="getUserRecommendations",
     *     tags={"users"},
     *
     *     @OA\Parameter(
     *       name="username",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns Recent Anime Recommendations",
     *         @OA\JsonContent(ref="#/components/schemas/recommendations")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     *
     */
    public function recommendations(Request $request, string $username)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $page = $request->get('page') ?? 1;
            $data = $this->jikan->getUserRecommendations(new UserRecommendationsRequest($username, $page));
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

    /**
     *  @OA\Get(
     *     path="/users/{username}/clubs",
     *     operationId="getUserClubs",
     *     tags={"users"},
     *
     *     @OA\Parameter(
     *       name="username",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns user clubs",
     *         @OA\JsonContent(ref="#/components/schemas/user clubs")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     *
     *  @OA\Schema(
     *      schema="user clubs",
     *      description="User Clubs",
     *
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/pagination"),
     *          @OA\Schema(
     *
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
     *                          property="name",
     *                          type="string",
     *                          description="Club Name"
     *                      ),
     *                      @OA\Property(
     *                          property="url",
     *                          type="string",
     *                          description="Club URL"
     *                      ),
     *                  ),
     *              ),
     *          ),
     *     }
     *  ),
     */
    public function clubs(Request $request, string $username)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $data = ['results' => $this->jikan->getUserClubs(new UserClubsRequest($username))];
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

    public function recentlyOnline(Request $request)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $data = ['results'=>$this->jikan->getRecentOnlineUsers(new RecentlyOnlineUsersRequest())];
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

    private function listStatusToId(?string $status) : int
    {
        if (is_null($status)) {
            return 7;
        }

        switch ($status) {
            case 'all':
                return 7;
            case 'watching':
            case 'reading':
                return 1;
            case 'completed':
                return 2;
            case 'onhold':
                return 3;
            case 'dropped':
                return 4;
            case 'plantowatch':
            case 'ptw':
            case 'plantoread':
            case 'ptr':
                return 6;
            default:
                return 7;
        }
    }
}
