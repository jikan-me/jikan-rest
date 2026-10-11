<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\QueryAnimeSeasonListCommand;
use App\Dto\QueryCurrentAnimeSeasonCommand;
use App\Dto\QuerySpecificAnimeSeasonCommand;
use App\Dto\QueryUpcomingAnimeSeasonCommand;
use Exception;
use OpenApi\Attributes as OA;

class SeasonController extends Controller
{
    /**
     * @throws Exception
     */
    #[OA\Get(
        path: '/seasons/now',
        operationId: 'getSeasonNow',
        summary: 'Current anime season',
        description: 'Returns the anime currently airing this season. Accepts an optional **filter**.',
        tags: ['seasons'],
        parameters: [
            new OA\Parameter(
                name: 'filter',
                description: 'Entry types',
                in: 'query',
                schema: new OA\Schema(type: 'string', enum: ['tv', 'movie', 'ova', 'special', 'ona', 'music']),
            ),
            new OA\Parameter(ref: '#/components/parameters/sfw'),
            new OA\Parameter(ref: '#/components/parameters/unapproved'),
            new OA\Parameter(ref: '#/components/parameters/continuing'),
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns current seasonal anime',
                content: new OA\JsonContent(ref: '#/components/schemas/anime_search'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function now(QueryCurrentAnimeSeasonCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    /**
     * @throws Exception
     */
    #[OA\Get(
        path: '/seasons/{year}/{season}',
        operationId: 'getSeason',
        summary: 'Seasonal anime',
        description: 'Returns the anime for a specific year and season, filtered by the **filter** parameter.',
        tags: ['seasons'],
        parameters: [
            new OA\Parameter(
                name: 'year',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(
                name: 'season',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'filter',
                description: 'Entry types',
                in: 'query',
                schema: new OA\Schema(type: 'string', enum: ['tv', 'movie', 'ova', 'special', 'ona', 'music']),
            ),
            new OA\Parameter(ref: '#/components/parameters/sfw'),
            new OA\Parameter(ref: '#/components/parameters/unapproved'),
            new OA\Parameter(ref: '#/components/parameters/continuing'),
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns seasonal anime',
                content: new OA\JsonContent(ref: '#/components/schemas/anime_search'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function main(QuerySpecificAnimeSeasonCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/seasons',
        operationId: 'getSeasonsList',
        summary: 'Seasons list',
        description: 'Lists the available seasonal anime indexes.',
        tags: ['seasons'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns available list of seasons',
                content: new OA\JsonContent(ref: '#/components/schemas/seasons'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    
    #[OA\Schema(
        schema: 'seasons',
        description: 'List of available seasons',
        properties: [
            new OA\Property(
                property: 'data',
                type: 'array',
                items: new OA\Items(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'year', type: 'integer', description: 'Year'),
                        new OA\Property(
                            property: 'seasons',
                            type: 'array',
                            description: 'List of available seasons',
                            items: new OA\Items(type: 'string'),
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function archive(QueryAnimeSeasonListCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    /**
     * @throws Exception
     */
    #[OA\Get(
        path: '/seasons/upcoming',
        operationId: 'getSeasonUpcoming',
        summary: 'Upcoming anime season',
        description: 'Returns the upcoming anime season. Accepts an optional **filter**.',
        tags: ['seasons'],
        parameters: [
            new OA\Parameter(
                name: 'filter',
                description: 'Entry types',
                in: 'query',
                schema: new OA\Schema(type: 'string', enum: ['tv', 'movie', 'ova', 'special', 'ona', 'music']),
            ),
            new OA\Parameter(ref: '#/components/parameters/sfw'),
            new OA\Parameter(ref: '#/components/parameters/unapproved'),
            new OA\Parameter(ref: '#/components/parameters/continuing'),
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns upcoming season\'s anime',
                content: new OA\JsonContent(ref: '#/components/schemas/anime_search'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function later(QueryUpcomingAnimeSeasonCommand $command): mixed
    {
        return $this->mediator->send($command);
    }
}
