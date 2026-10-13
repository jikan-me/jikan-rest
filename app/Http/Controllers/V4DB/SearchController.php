<?php

namespace App\Http\Controllers\V4DB;

use App\Contracts\DataRequest;
use App\Dto\AnimeSearchCommand;
use App\Dto\CharactersSearchCommand;
use App\Dto\ClubSearchCommand;
use App\Dto\MangaSearchCommand;
use App\Dto\PeopleSearchCommand;
use App\Dto\ProducersSearchCommand;
use App\Dto\UserByIdLookupCommand;
use App\Dto\UsersSearchCommand;
use OpenApi\Attributes as OA;

class SearchController extends Controller
{
    #[OA\Get(
        path: '/anime',
        operationId: 'getAnimeSearch',
        summary: 'Anime search',
        description: 'Searches the anime catalog. Accepts filters such as **q**, **type**, **score**, **status**, **rating**, **genres**, **order_by**, and **sort**, with pagination via **page**.',
        tags: ['anime'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/unapproved'),
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(
                name: 'q',
                in: 'query',
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'type',
                in: 'query',
                schema: new OA\Schema(ref: '#/components/schemas/anime_search_query_type'),
            ),
            new OA\Parameter(
                name: 'score',
                in: 'query',
                schema: new OA\Schema(type: 'number'),
            ),
            new OA\Parameter(
                name: 'min_score',
                description: 'Set a minimum score for results.',
                in: 'query',
                schema: new OA\Schema(type: 'number'),
            ),
            new OA\Parameter(
                name: 'max_score',
                description: 'Set a maximum score for results',
                in: 'query',
                schema: new OA\Schema(type: 'number'),
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                schema: new OA\Schema(ref: '#/components/schemas/anime_search_query_status'),
            ),
            new OA\Parameter(
                name: 'rating',
                in: 'query',
                schema: new OA\Schema(ref: '#/components/schemas/anime_search_query_rating'),
            ),
            new OA\Parameter(
                name: 'sfw',
                in: 'query',
                description: 'Filter out Adult entries',
                schema: new OA\Schema(type: 'boolean'),
            ),
            new OA\Parameter(
                name: 'genres',
                in: 'query',
                description: 'Filter by genre(s) IDs. Can pass multiple with a comma as a delimiter. e.g 1,2,3',
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'genres_exclude',
                in: 'query',
                description: 'Exclude genre(s) IDs. Can pass multiple with a comma as a delimiter. e.g 1,2,3',
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'order_by',
                in: 'query',
                schema: new OA\Schema(ref: '#/components/schemas/anime_search_query_orderby'),
            ),
            new OA\Parameter(
                name: 'sort',
                in: 'query',
                schema: new OA\Schema(ref: '#/components/schemas/search_query_sort'),
            ),
            new OA\Parameter(
                name: 'letter',
                in: 'query',
                description: 'Return entries starting with the given letter',
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'producers',
                in: 'query',
                description: 'Filter by producer(s) IDs. Can pass multiple with a comma as a delimiter. e.g 1,2,3',
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'start_date',
                in: 'query',
                description: 'Filter by starting date. Format: YYYY-MM-DD. e.g `2022`, `2005-05`, `2005-01-01`',
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'end_date',
                in: 'query',
                description: 'Filter by ending date. Format: YYYY-MM-DD. e.g `2022`, `2005-05`, `2005-01-01`',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns search results for anime',
                content: new OA\JsonContent(ref: '#/components/schemas/anime_search'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function anime(AnimeSearchCommand $request): mixed
    {
        return $this->runSearch($request);
    }

    #[OA\Get(
        path: '/manga',
        operationId: 'getMangaSearch',
        summary: 'Manga search',
        description: 'Searches the manga catalog. Accepts filters such as **q**, **type**, **score**, **status**, **genres**, **order_by**, and **sort**, with pagination via **page**.',
        tags: ['manga'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/unapproved'),
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(
                name: 'q',
                in: 'query',
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'type',
                in: 'query',
                schema: new OA\Schema(ref: '#/components/schemas/manga_search_query_type'),
            ),
            new OA\Parameter(
                name: 'score',
                in: 'query',
                schema: new OA\Schema(type: 'number'),
            ),
            new OA\Parameter(
                name: 'min_score',
                description: 'Set a minimum score for results.',
                in: 'query',
                schema: new OA\Schema(type: 'number'),
            ),
            new OA\Parameter(
                name: 'max_score',
                description: 'Set a maximum score for results',
                in: 'query',
                schema: new OA\Schema(type: 'number'),
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                schema: new OA\Schema(ref: '#/components/schemas/manga_search_query_status'),
            ),
            new OA\Parameter(
                name: 'sfw',
                in: 'query',
                description: 'Filter out Adult entries',
                schema: new OA\Schema(type: 'boolean'),
            ),
            new OA\Parameter(
                name: 'genres',
                in: 'query',
                description: 'Filter by genre(s) IDs. Can pass multiple with a comma as a delimiter. e.g 1,2,3',
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'genres_exclude',
                in: 'query',
                description: 'Exclude genre(s) IDs. Can pass multiple with a comma as a delimiter. e.g 1,2,3',
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'order_by',
                in: 'query',
                schema: new OA\Schema(ref: '#/components/schemas/manga_search_query_orderby'),
            ),
            new OA\Parameter(
                name: 'sort',
                in: 'query',
                schema: new OA\Schema(ref: '#/components/schemas/search_query_sort'),
            ),
            new OA\Parameter(
                name: 'letter',
                in: 'query',
                description: 'Return entries starting with the given letter',
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'magazines',
                in: 'query',
                description: 'Filter by magazine(s) IDs. Can pass multiple with a comma as a delimiter. e.g 1,2,3',
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'start_date',
                in: 'query',
                description: 'Filter by starting date. Format: YYYY-MM-DD. e.g `2022`, `2005-05`, `2005-01-01`',
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'end_date',
                in: 'query',
                description: 'Filter by ending date. Format: YYYY-MM-DD. e.g `2022`, `2005-05`, `2005-01-01`',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns search results for manga',
                content: new OA\JsonContent(ref: '#/components/schemas/manga_search'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function manga(MangaSearchCommand $request): mixed
    {
        return $this->runSearch($request);
    }

    #[OA\Get(
        path: '/people',
        operationId: 'getPeopleSearch',
        summary: 'People search',
        description: 'Searches the people catalog using the **q**, **order_by**, **sort**, and **letter** filters. Supports pagination via **page**.',
        tags: ['people'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(
                name: 'q',
                in: 'query',
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'order_by',
                in: 'query',
                schema: new OA\Schema(ref: '#/components/schemas/people_search_query_orderby'),
            ),
            new OA\Parameter(
                name: 'sort',
                in: 'query',
                schema: new OA\Schema(ref: '#/components/schemas/search_query_sort'),
            ),
            new OA\Parameter(
                name: 'letter',
                in: 'query',
                description: 'Return entries starting with the given letter',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns search results for people',
                content: new OA\JsonContent(ref: '#/components/schemas/people_search'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function people(PeopleSearchCommand $request): mixed
    {
        return $this->runSearch($request);
    }

    #[OA\Get(
        path: '/characters',
        operationId: 'getCharactersSearch',
        summary: 'Characters search',
        description: 'Searches the characters catalog using the **q**, **order_by**, **sort**, and **letter** filters. Supports pagination via **page**.',
        tags: ['characters'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(
                name: 'q',
                in: 'query',
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'order_by',
                in: 'query',
                schema: new OA\Schema(ref: '#/components/schemas/characters_search_query_orderby'),
            ),
            new OA\Parameter(
                name: 'sort',
                in: 'query',
                schema: new OA\Schema(ref: '#/components/schemas/search_query_sort'),
            ),
            new OA\Parameter(
                name: 'letter',
                in: 'query',
                description: 'Return entries starting with the given letter',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns search results for characters',
                content: new OA\JsonContent(ref: '#/components/schemas/characters_search'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function character(CharactersSearchCommand $request): mixed
    {
        return $this->runSearch($request);
    }

    #[OA\Get(
        path: '/users',
        operationId: 'getUsersSearch',
        summary: 'Users search',
        description: 'Searches the users catalog using the **q**, **gender**, **location**, **maxAge**, and **minAge** filters. Supports pagination via **page**.',
        tags: ['users'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(
                name: 'q',
                in: 'query',
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'gender',
                in: 'query',
                schema: new OA\Schema(ref: '#/components/schemas/users_search_query_gender'),
            ),
            new OA\Parameter(
                name: 'location',
                in: 'query',
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'maxAge',
                in: 'query',
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(
                name: 'minAge',
                in: 'query',
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns search results for users',
                content: new OA\JsonContent(ref: '#/components/schemas/users_search'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]

    #[OA\Schema(schema: 'users_search', description: 'User Results', allOf: [new OA\Schema(ref: '#/components/schemas/pagination'), new OA\Schema(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object', properties: [new OA\Property(property: 'url', type: 'string', description: 'MyAnimeList URL'), new OA\Property(property: 'username', type: 'string', description: 'MyAnimeList Username'), new OA\Property(property: 'images', type: 'object', ref: '#/components/schemas/user_images'), new OA\Property(property: 'last_online', type: 'string', description: 'Last Online Date ISO8601')]))])])]
    public function users(UsersSearchCommand $request): mixed
    {
        return $this->runSearch($request);
    }

    #[OA\Get(
        path: '/users/userbyid/{id}',
        operationId: 'getUserById',
        summary: 'User by ID',
        description: 'Returns a user profile by their MyAnimeList **id**.',
        tags: ['users'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns username by ID search',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/user_by_id'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function userById(UserByIdLookupCommand $command): mixed
    {
        return $this->runSearch($command);
    }

    #[OA\Get(
        path: '/clubs',
        operationId: 'getClubsSearch',
        summary: 'Clubs search',
        description: 'Searches the clubs catalog using the **q**, **type**, **category**, **order_by**, **sort**, and **letter** filters. Supports pagination via **page**.',
        tags: ['clubs'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(
                name: 'q',
                in: 'query',
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'type',
                in: 'query',
                schema: new OA\Schema(ref: '#/components/schemas/club_search_query_type'),
            ),
            new OA\Parameter(
                name: 'category',
                in: 'query',
                schema: new OA\Schema(ref: '#/components/schemas/club_search_query_category'),
            ),
            new OA\Parameter(
                name: 'order_by',
                in: 'query',
                schema: new OA\Schema(ref: '#/components/schemas/club_search_query_orderby'),
            ),
            new OA\Parameter(
                name: 'sort',
                in: 'query',
                schema: new OA\Schema(ref: '#/components/schemas/search_query_sort'),
            ),
            new OA\Parameter(
                name: 'letter',
                in: 'query',
                description: 'Return entries starting with the given letter',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns search results for clubs',
                content: new OA\JsonContent(ref: '#/components/schemas/clubs_search'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function clubs(ClubSearchCommand $request): mixed
    {
        return $this->runSearch($request);
    }

    #[OA\Get(
        path: '/producers',
        operationId: 'getProducers',
        summary: 'Producers list',
        description: 'Searches the producers catalog using the **q**, **order_by**, **sort**, and **letter** filters. Supports pagination via **page**.',
        tags: ['producers'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(
                name: 'q',
                in: 'query',
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'order_by',
                in: 'query',
                schema: new OA\Schema(ref: '#/components/schemas/producers_query_orderby'),
            ),
            new OA\Parameter(
                name: 'sort',
                in: 'query',
                schema: new OA\Schema(ref: '#/components/schemas/search_query_sort'),
            ),
            new OA\Parameter(
                name: 'letter',
                in: 'query',
                description: 'Return entries starting with the given letter',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns producers collection',
                content: new OA\JsonContent(ref: '#/components/schemas/producers'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function producers(ProducersSearchCommand $request): mixed
    {
        return $this->runSearch($request);
    }

    /**
     * Search is best-effort: a missing/unreachable search (Typesense) collection
     * must render an EMPTY result (200) rather than 500 — any search failure is
     * degraded gracefully for the whole search surface.
     *
     * @template T of \Illuminate\Http\Resources\Json\ResourceCollection|\Illuminate\Http\Resources\Json\JsonResource|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     *
     * @param  DataRequest<T>  $command
     */
    private function runSearch(DataRequest $command): mixed
    {
        try {
            return $this->mediator->send($command);
        } catch (\Throwable $e) {
            try {
                report($e);
            } catch (\Throwable $ignore) {
            }

            return response()->json([
                'data' => [],
                'pagination' => [
                    'last_visible_page' => 1,
                    'has_next_page' => false,
                    'current_page' => 1,
                    'items' => ['count' => 0, 'total' => 0, 'per_page' => 25],
                ],
            ]);
        }
    }
}
