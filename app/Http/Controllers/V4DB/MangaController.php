<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\MangaCharactersLookupCommand;
use App\Dto\MangaExternalLookupCommand;
use App\Dto\MangaForumLookupCommand;
use App\Dto\MangaFullLookupCommand;
use App\Dto\MangaLookupCommand;
use App\Dto\MangaMoreInfoLookupCommand;
use App\Dto\MangaNewsLookupCommand;
use App\Dto\MangaPicturesLookupCommand;
use App\Dto\MangaRecommendationsLookupCommand;
use App\Dto\MangaRelationsLookupCommand;
use App\Dto\MangaReviewsLookupCommand;
use App\Dto\MangaStatsLookupCommand;
use App\Dto\MangaUserUpdatesLookupCommand;
use OpenApi\Attributes as OA;

class MangaController extends Controller
{
    #[OA\Get(
        path: '/manga/{id}/full',
        operationId: 'getMangaFullById',
        summary: 'Complete manga resource',
        description: 'Returns the complete manga resource, including primary data plus related sub-resources, for a manga by **id**.',
        tags: ['manga'],
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
                description: 'Returns complete manga resource data',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/manga_full'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function full(MangaFullLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/manga/{id}',
        operationId: 'getMangaById',
        summary: 'Manga details',
        description: 'Returns the details of a manga by **id**.',
        tags: ['manga'],
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
                description: 'Returns pictures related to the entry',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/manga'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function main(MangaLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/manga/{id}/characters',
        operationId: 'getMangaCharacters',
        summary: 'Manga characters',
        description: 'Returns the characters of a manga by **id**.',
        tags: ['manga'],
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
                description: 'Returns manga characters resource',
                content: new OA\JsonContent(ref: '#/components/schemas/manga_characters'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function characters(MangaCharactersLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/manga/{id}/news',
        operationId: 'getMangaNews',
        summary: 'Manga news',
        description: 'Returns recent news articles for a manga by **id**, paginated by **page**.',
        tags: ['manga'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(ref: '#/components/parameters/page'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns a list of manga news topics',
                content: new OA\JsonContent(ref: '#/components/schemas/manga_news'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    
    #[OA\Schema(schema: 'manga_news', description: 'Manga News Resource', allOf: [new OA\Schema(ref: '#/components/schemas/pagination'), new OA\Schema(ref: '#/components/schemas/news')])]
    public function news(MangaNewsLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/manga/{id}/forum',
        operationId: 'getMangaTopics',
        summary: 'Manga forum topics',
        description: 'Returns the forum topics of a manga by **id**. Use **filter** to restrict to all, episode, or other topics.',
        tags: ['manga'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(
                name: 'filter',
                in: 'query',
                required: false,
                description: 'Filter topics',
                schema: new OA\Schema(type: 'string', enum: ['all', 'episode', 'other']),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns a list of manga forum topics',
                content: new OA\JsonContent(ref: '#/components/schemas/forum'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function forum(MangaForumLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/manga/{id}/pictures',
        operationId: 'getMangaPictures',
        summary: 'Manga pictures',
        description: 'Returns the pictures of a manga by **id**.',
        tags: ['manga'],
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
                description: 'Returns a list of manga pictures',
                content: new OA\JsonContent(ref: '#/components/schemas/manga_pictures'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    
    #[OA\Schema(
        schema: 'manga_pictures',
        description: 'Manga Pictures',
        properties: [
            new OA\Property(
                property: 'data',
                type: 'array',
                items: new OA\Items(type: 'object', ref: '#/components/schemas/manga_images'),
            ),
        ],
    )]
    public function pictures(MangaPicturesLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/manga/{id}/statistics',
        operationId: 'getMangaStatistics',
        summary: 'Manga statistics',
        description: 'Returns the statistics of a manga by **id**, such as reading, completed, and on-hold counts.',
        tags: ['manga'],
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
                description: 'Returns anime statistics',
                content: new OA\JsonContent(ref: '#/components/schemas/manga_statistics'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function stats(MangaStatsLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/manga/{id}/moreinfo',
        operationId: 'getMangaMoreInfo',
        summary: 'Manga more info',
        description: 'Returns additional trivia and background information about a manga by **id**.',
        tags: ['manga'],
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
                description: 'Returns manga moreinfo',
                content: new OA\JsonContent(ref: '#/components/schemas/moreinfo'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function moreInfo(MangaMoreInfoLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/manga/{id}/recommendations',
        operationId: 'getMangaRecommendations',
        summary: 'Manga recommendations',
        description: 'Returns the recommendations for a manga by **id**.',
        tags: ['manga'],
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
                description: 'Returns manga recommendations',
                content: new OA\JsonContent(ref: '#/components/schemas/entry_recommendations'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function recommendations(MangaRecommendationsLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/manga/{id}/userupdates',
        operationId: 'getMangaUserUpdates',
        summary: 'Manga user updates',
        description: 'Returns the recent list updates of users for a manga by **id**, paginated by **page**.',
        tags: ['manga'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(ref: '#/components/parameters/page'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns manga user updates',
                content: new OA\JsonContent(ref: '#/components/schemas/manga_userupdates'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function userupdates(MangaUserUpdatesLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    /**
     * @throws \Exception
     */
    #[OA\Get(
        path: '/manga/{id}/reviews',
        operationId: 'getMangaReviews',
        summary: 'Manga reviews',
        description: 'Returns the reviews of a manga by **id**. Supports pagination via **page**, with **preliminary** and **spoilers** toggles for review types.',
        tags: ['manga'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/preliminary'),
            new OA\Parameter(ref: '#/components/parameters/spoilers'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns manga reviews',
                content: new OA\JsonContent(ref: '#/components/schemas/manga_reviews'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function reviews(MangaReviewsLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/manga/{id}/relations',
        operationId: 'getMangaRelations',
        summary: 'Manga relations',
        description: 'Returns the related entries for a manga by **id**.',
        tags: ['manga'],
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
                description: 'Returns manga relations',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/relation'),
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function relations(MangaRelationsLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/manga/{id}/external',
        operationId: 'getMangaExternal',
        summary: 'Manga external links',
        description: 'Returns the external links of a manga by **id**.',
        tags: ['manga'],
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
                description: 'Returns manga external links',
                content: new OA\JsonContent(ref: '#/components/schemas/external_links'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function external(MangaExternalLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }
}
