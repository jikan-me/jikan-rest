<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\QueryTopAnimeItemsCommand;
use App\Dto\QueryTopCharactersCommand;
use App\Dto\QueryTopMangaItemsCommand;
use App\Dto\QueryTopPeopleCommand;
use App\Dto\QueryTopReviewsCommand;
use OpenApi\Attributes as OA;

class TopController extends Controller
{
    #[OA\Get(
        path: '/top/anime',
        operationId: 'getTopAnime',
        summary: 'Top anime',
        description: 'Returns the top-ranked anime. Can be filtered by **type**, **filter**, **rating**, and **sfw**.',
        tags: ['top'],
        parameters: [
            new OA\Parameter(
                name: 'type',
                in: 'query',
                required: false,
                schema: new OA\Schema(ref: '#/components/schemas/anime_search_query_type'),
            ),
            new OA\Parameter(
                name: 'filter',
                in: 'query',
                required: false,
                schema: new OA\Schema(ref: '#/components/schemas/top_anime_filter'),
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
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns top anime',
                content: new OA\JsonContent(ref: '#/components/schemas/anime_search'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function anime(QueryTopAnimeItemsCommand $request): mixed
    {
        return $this->mediator->send($request);
    }

    #[OA\Get(
        path: '/top/manga',
        operationId: 'getTopManga',
        summary: 'Top manga',
        description: 'Returns the top-ranked manga. Can be filtered by **type** and **filter**.',
        tags: ['top'],
        parameters: [
            new OA\Parameter(
                name: 'type',
                in: 'query',
                required: false,
                schema: new OA\Schema(ref: '#/components/schemas/manga_search_query_type'),
            ),
            new OA\Parameter(
                name: 'filter',
                in: 'query',
                required: false,
                schema: new OA\Schema(ref: '#/components/schemas/top_manga_filter'),
            ),
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns top manga',
                content: new OA\JsonContent(ref: '#/components/schemas/manga_search'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function manga(QueryTopMangaItemsCommand $request): mixed
    {
        return $this->mediator->send($request);
    }

    #[OA\Get(
        path: '/top/people',
        operationId: 'getTopPeople',
        summary: 'Top people',
        description: 'Returns the top-ranked people.',
        tags: ['top'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns top people',
                content: new OA\JsonContent(ref: '#/components/schemas/people_search'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function people(QueryTopPeopleCommand $request): mixed
    {
        return $this->mediator->send($request);
    }

    #[OA\Get(
        path: '/top/characters',
        operationId: 'getTopCharacters',
        summary: 'Top characters',
        description: 'Returns the top-ranked characters.',
        tags: ['top'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns top characters',
                content: new OA\JsonContent(ref: '#/components/schemas/characters_search'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function characters(QueryTopCharactersCommand $request): mixed
    {
        return $this->mediator->send($request);
    }

    #[OA\Get(
        path: '/top/reviews',
        operationId: 'getTopReviews',
        summary: 'Top reviews',
        description: 'Returns the top-ranked reviews. Can be filtered by **type**, with **preliminary** and **spoilers** toggles.',
        tags: ['top'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(
                name: 'type',
                in: 'query',
                required: false,
                schema: new OA\Schema(ref: '#/components/schemas/top_reviews_type_enum'),
            ),
            new OA\Parameter(
                name: 'preliminary',
                in: 'query',
                required: false,
                description: 'Whether the results include preliminary reviews or not. Defaults to true.',
                schema: new OA\Schema(type: 'boolean'),
            ),
            new OA\Parameter(
                name: 'spoilers',
                in: 'query',
                required: false,
                description: 'Whether the results include reviews with spoilers or not. Defaults to true.',
                schema: new OA\Schema(type: 'boolean'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns top reviews',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', allOf: [new OA\Schema(ref: '#/components/schemas/pagination'), new OA\Schema(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(anyOf: [new OA\Schema(allOf: [new OA\Schema(ref: '#/components/schemas/anime_review'), new OA\Schema(properties: [new OA\Property(property: 'anime', type: 'object', ref: '#/components/schemas/anime_meta')]), new OA\Schema(properties: [new OA\Property(property: 'user', type: 'object', ref: '#/components/schemas/user_meta')])]), new OA\Schema(allOf: [new OA\Schema(ref: '#/components/schemas/manga_review'), new OA\Schema(properties: [new OA\Property(property: 'manga', type: 'object', ref: '#/components/schemas/manga_meta')]), new OA\Schema(properties: [new OA\Property(property: 'user', type: 'object', ref: '#/components/schemas/user_meta')])])]))])]),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]

    #[OA\Schema(
        schema: 'reviews_collection',
        description: 'Anime & Manga Reviews Resource',
        properties: [
            new OA\Property(
                property: 'data',
                type: 'array',
                items: new OA\Items(type: 'object', anyOf: [new OA\Schema(ref: '#/components/schemas/anime_review'), new OA\Schema(ref: '#/components/schemas/manga_review')]),
            ),
        ],
    )]
    public function reviews(QueryTopReviewsCommand $request): mixed
    {
        return $this->mediator->send($request);
    }
}
