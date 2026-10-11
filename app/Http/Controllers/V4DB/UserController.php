<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\QueryAnimeListOfUserCommand;
use App\Dto\QueryMangaListOfUserCommand;
use App\Dto\QueryRecentlyOnlineUsersCommand;
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
use OpenApi\Attributes as OA;

/**
 * Class Controller
 */
class UserController extends Controller
{
    #[OA\Get(
        path: '/users/{username}/full',
        operationId: 'getUserFullProfile',
        summary: 'Complete user profile',
        description: 'Retrieves the complete user profile for the user identified by **username**.',
        tags: ['users'],
        parameters: [
            new OA\Parameter(
                name: 'username',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns complete user resource data',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/user_profile_full'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function full(UserFullLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/users/{username}',
        operationId: 'getUserProfile',
        summary: 'User profile',
        description: 'Retrieves the user profile for the user identified by **username**.',
        tags: ['users'],
        parameters: [
            new OA\Parameter(
                name: 'username',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns user profile',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/user_profile'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function profile(UserProfileLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/users/{username}/statistics',
        operationId: 'getUserStatistics',
        summary: 'User statistics',
        description: 'Retrieves the user statistics for the user identified by **username**.',
        tags: ['users'],
        parameters: [
            new OA\Parameter(
                name: 'username',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns user statistics',
                content: new OA\JsonContent(ref: '#/components/schemas/user_statistics'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function statistics(UserStatisticsLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/users/{username}/favorites',
        operationId: 'getUserFavorites',
        summary: 'User favorites',
        description: 'Retrieves the user favorites for the user identified by **username**.',
        tags: ['users'],
        parameters: [
            new OA\Parameter(
                name: 'username',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns user favorites',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/user_favorites'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function favorites(UserFavoritesLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/users/{username}/userupdates',
        operationId: 'getUserUpdates',
        summary: 'User updates',
        description: 'Retrieves the user updates for the user identified by **username**.',
        tags: ['users'],
        parameters: [
            new OA\Parameter(
                name: 'username',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns user updates',
                content: new OA\JsonContent(ref: '#/components/schemas/user_updates'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function userupdates(UserUpdatesLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/users/{username}/about',
        operationId: 'getUserAbout',
        summary: 'User about',
        description: 'Retrieves the user about for the user identified by **username**.',
        tags: ['users'],
        parameters: [
            new OA\Parameter(
                name: 'username',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns user about in raw HTML',
                content: new OA\JsonContent(ref: '#/components/schemas/user_about'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function about(UserAboutLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/users/{username}/history',
        operationId: 'getUserHistory',
        summary: 'User history',
        description: 'Retrieves the user history for the user identified by **username**. Filters: **type**.',
        tags: ['users'],
        parameters: [
            new OA\Parameter(
                name: 'username',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'type',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['anime', 'manga']),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns user history (past 30 days)',
                content: new OA\JsonContent(ref: '#/components/schemas/user_history'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function history(UserHistoryLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/users/{username}/friends',
        operationId: 'getUserFriends',
        summary: 'User friends',
        description: 'Retrieves the user friends for the user identified by **username**.',
        tags: ['users'],
        parameters: [
            new OA\Parameter(
                name: 'username',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(ref: '#/components/parameters/page'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns user friends',
                content: new OA\JsonContent(ref: '#/components/schemas/user_friends'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    
    #[OA\Schema(schema: 'user_friends', description: 'User Friends', allOf: [new OA\Schema(ref: '#/components/schemas/pagination'), new OA\Schema(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object', allOf: [new OA\Schema(properties: [new OA\Property(property: 'user', type: 'object', ref: '#/components/schemas/user_meta')]), new OA\Schema(properties: [new OA\Property(property: 'last_online', type: 'string', description: 'Last Online Date ISO8601 format'), new OA\Property(property: 'friends_since', type: 'string', description: 'Friends Since Date ISO8601 format')])]))])])]
    public function friends(UserFriendsLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/users/{username}/animelist',
        operationId: 'getUserAnimelist',
        summary: 'User animelist',
        description: 'Retrieves the user animelist for the user identified by **username**. Filters: **status**.

User Anime lists have been discontinued since May 1st, 2022. <a href=\'https://docs.google.com/document/d/1-6H-agSnqa8Mfmw802UYfGQrceIEnAaEh4uCXAPiX5A\'>Read more</a>',
        tags: ['users'],
        deprecated: true,
        parameters: [
            new OA\Parameter(
                name: 'username',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                schema: new OA\Schema(ref: '#/components/schemas/user_anime_list_status_filter'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns user anime list',
                content: new OA\JsonContent(),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function animelist(QueryAnimeListOfUserCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/users/{username}/mangalist',
        operationId: 'getUserMangaList',
        summary: 'User manga list',
        description: 'Retrieves the user manga list for the user identified by **username**. Filters: **status**.

User Manga lists have been discontinued since May 1st, 2022. <a href=\'https://docs.google.com/document/d/1-6H-agSnqa8Mfmw802UYfGQrceIEnAaEh4uCXAPiX5A\'>Read more</a>',
        tags: ['users'],
        deprecated: true,
        parameters: [
            new OA\Parameter(
                name: 'username',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                schema: new OA\Schema(ref: '#/components/schemas/user_manga_list_status_filter'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns user manga list',
                content: new OA\JsonContent(),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function mangalist(QueryMangaListOfUserCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/users/{username}/reviews',
        operationId: 'getUserReviews',
        summary: 'User reviews',
        description: 'Retrieves the user reviews for the user identified by **username**.',
        tags: ['users'],
        parameters: [
            new OA\Parameter(
                name: 'username',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(ref: '#/components/parameters/page'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns user reviews',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', allOf: [new OA\Schema(ref: '#/components/schemas/pagination'), new OA\Schema(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(anyOf: [new OA\Schema(allOf: [new OA\Schema(ref: '#/components/schemas/anime_review'), new OA\Schema(properties: [new OA\Property(property: 'anime', type: 'object', ref: '#/components/schemas/anime_meta')]), new OA\Schema(properties: [new OA\Property(property: 'user', type: 'object', ref: '#/components/schemas/user_meta')])]), new OA\Schema(allOf: [new OA\Schema(ref: '#/components/schemas/manga_review'), new OA\Schema(properties: [new OA\Property(property: 'manga', type: 'object', ref: '#/components/schemas/manga_meta')]), new OA\Schema(properties: [new OA\Property(property: 'user', type: 'object', ref: '#/components/schemas/user_meta')])])]))])]),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function reviews(UserReviewsLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/users/{username}/recommendations',
        operationId: 'getUserRecommendations',
        summary: 'User recommendations',
        description: 'Retrieves the user recommendations for the user identified by **username**.',
        tags: ['users'],
        parameters: [
            new OA\Parameter(
                name: 'username',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(ref: '#/components/parameters/page'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns Recent Anime Recommendations',
                content: new OA\JsonContent(ref: '#/components/schemas/recommendations'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function recommendations(UserRecommendationsLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/users/{username}/clubs',
        operationId: 'getUserClubs',
        summary: 'User clubs',
        description: 'Retrieves the user clubs for the user identified by **username**.',
        tags: ['users'],
        parameters: [
            new OA\Parameter(
                name: 'username',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(ref: '#/components/parameters/page'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns user clubs',
                content: new OA\JsonContent(ref: '#/components/schemas/user_clubs'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    
    #[OA\Schema(schema: 'user_clubs', description: 'User Clubs', allOf: [new OA\Schema(ref: '#/components/schemas/pagination'), new OA\Schema(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object', properties: [new OA\Property(property: 'mal_id', type: 'integer', description: 'MyAnimeList ID'), new OA\Property(property: 'name', type: 'string', description: 'Club Name'), new OA\Property(property: 'url', type: 'string', description: 'Club URL')]))])])]
    public function clubs(UserClubsLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/users/{username}/external',
        operationId: 'getUserExternal',
        summary: 'User external links',
        description: 'Retrieves the user external links for the user identified by **username**.',
        tags: ['users'],
        parameters: [
            new OA\Parameter(
                name: 'username',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns user\'s external links',
                content: new OA\JsonContent(ref: '#/components/schemas/external_links'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function external(UserExternalLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/users/recentlyonline',
        operationId: 'getRecentlyOnlineUsers',
        summary: 'Recently online users',
        description: 'Returns the users currently online or recently online.',
        tags: ['users'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns recently online users',
                content: new OA\JsonContent(
                    type: 'object',
                    allOf: [
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(
                                        allOf: [
                                            new OA\Schema(ref: '#/components/schemas/basic_user'),
                                        ],
                                    ),
                                ),
                            ],
                        ),
                    ],
                ),
            ),
        ],
    )]

    #[OA\Schema(schema: 'basic_user', type: 'object', description: 'Basic user meta data', properties: [
        new OA\Property(property: 'username', type: 'string', description: 'MyAnimeList Username'),
        new OA\Property(property: 'url', type: 'string', description: 'MyAnimeList URL'),
    ])]
    public function recentlyOnline(QueryRecentlyOnlineUsersCommand $command): mixed
    {
        return $this->mediator->send($command);
    }
}
