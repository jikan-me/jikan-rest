<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\QueryMangaListOfUserCommand;
use App\Dto\QueryRecentlyOnlineUsersCommand;
use App\Dto\QueryAnimeListOfUserCommand;
use App\Dto\UserAboutLookupCommand;
use App\Dto\UserClubsLookupCommand;
use App\Dto\UserExternalLookupCommand;
use App\Dto\UserFavoritesLookupCommand;
use App\Dto\UserFriendsLookupCommand;
use App\Dto\UserFullLookupCommand;
use App\Dto\UserHistoryLookupCommand;
use App\Dto\UserProfileLookupCommand;
use App\Dto\UserRecommendationsLookupCommand;
use App\Dto\UserReviewsLookupCommand;
use App\Dto\UserStatisticsLookupCommand;
use App\Dto\UserUpdatesLookupCommand;
use Illuminate\Http\Request;

/**
 * Class Controller
 * @package App\Http\Controllers\V4DB
 */
class UserController extends Controller
{

    /**
     *  @OA\Get(
     *     path="/users/{username}/full",
     *     operationId="getUserFullProfile",
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
     *         description="Returns complete user resource data",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/user_profile_full"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function full(UserFullLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

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
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/user_profile"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function profile(UserProfileLookupCommand $command)
    {
        return $this->mediator->send($command);
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
     *              ref="#/components/schemas/user_statistics"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function statistics(UserStatisticsLookupCommand $command)
    {
        return $this->mediator->send($command);
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
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/user_favorites"
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function favorites(UserFavoritesLookupCommand $command)
    {
        return $this->mediator->send($command);
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
     *              ref="#/components/schemas/user_updates"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function userupdates(UserUpdatesLookupCommand $command)
    {
        return $this->mediator->send($command);
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
     *              ref="#/components/schemas/user_about"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function about(UserAboutLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/users/{username}/history",
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
     *       in="query",
     *       required=false,
     *       @OA\Schema(type="string",enum={"anime", "manga"})
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns user history (past 30 days)",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/user_history"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function history(UserHistoryLookupCommand $command)
    {
        return $this->mediator->send($command);
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
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns user friends",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/user_friends"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     *
     *  @OA\Schema(
     *      schema="user_friends",
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
     *                                  ref="#/components/schemas/user_meta"
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
    public function friends(UserFriendsLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/users/{username}/animelist",
     *     operationId="getUserAnimelist",
     *     tags={"users"},
     *     deprecated=true,
     *     description="User Anime lists have been discontinued since May 1st, 2022. <a href='https://docs.google.com/document/d/1-6H-agSnqa8Mfmw802UYfGQrceIEnAaEh4uCXAPiX5A'>Read more</a>",
     *
     *     @OA\Parameter(
     *       name="username",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *       name="status",
     *       in="query",
     *       @OA\Schema(ref="#/components/schemas/user_anime_list_status_filter")
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
    public function animelist(QueryAnimeListOfUserCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/users/{username}/mangalist",
     *     operationId="getUserMangaList",
     *     tags={"users"},
     *     deprecated=true,
     *     description="User Manga lists have been discontinued since May 1st, 2022. <a href='https://docs.google.com/document/d/1-6H-agSnqa8Mfmw802UYfGQrceIEnAaEh4uCXAPiX5A'>Read more</a>",
     *
     *     @OA\Parameter(
     *       name="username",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *       name="status",
     *       in="query",
     *       @OA\Schema(ref="#/components/schemas/user_manga_list_status_filter")
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
    public function mangalist(QueryMangaListOfUserCommand $command)
    {
        return $this->mediator->send($command);
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
     *      @OA\Parameter(ref="#/components/parameters/page"),
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
     */
    public function reviews(UserReviewsLookupCommand $command)
    {
        return $this->mediator->send($command);
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
     *      @OA\Parameter(ref="#/components/parameters/page"),
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
    public function recommendations(UserRecommendationsLookupCommand $command)
    {
        return $this->mediator->send($command);
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
     *      @OA\Parameter(ref="#/components/parameters/page"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns user clubs",
     *         @OA\JsonContent(ref="#/components/schemas/user_clubs")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     *
     *  @OA\Schema(
     *      schema="user_clubs",
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
    public function clubs(UserClubsLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/users/{username}/external",
     *     operationId="getUserExternal",
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
     *         description="Returns user's external links",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/external_links"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function external(UserExternalLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    public function recentlyOnline(QueryRecentlyOnlineUsersCommand $command)
    {
        return $this->mediator->send($command);
    }
}
