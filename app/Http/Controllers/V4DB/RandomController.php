<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\QueryRandomAnimeCommand;
use App\Dto\QueryRandomCharacterCommand;
use App\Dto\QueryRandomMangaCommand;
use App\Dto\QueryRandomPersonCommand;
use App\Dto\QueryRandomUserCommand;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'random',
    description: 'Random Resources',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(type: 'object', anyOf: [new OA\Schema(ref: '#/components/schemas/anime'), new OA\Schema(ref: '#/components/schemas/manga'), new OA\Schema(ref: '#/components/schemas/character'), new OA\Schema(ref: '#/components/schemas/person')]),
        ),
    ],
)]
class RandomController extends Controller
{
    /**
     * }
     * ),
     */

    #[OA\Get(
        path: '/random/anime',
        operationId: 'getRandomAnime',
        summary: 'Random anime',
        description: 'Returns a random anime from the indexed catalog. Outcome varies per request.',
        tags: ['random'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns a random anime resource',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/anime'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function anime(QueryRandomAnimeCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/random/manga',
        operationId: 'getRandomManga',
        summary: 'Random manga',
        description: 'Returns a random manga from the indexed catalog. Outcome varies per request.',
        tags: ['random'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns a random manga resource',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/manga'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function manga(QueryRandomMangaCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/random/characters',
        operationId: 'getRandomCharacters',
        summary: 'Random characters',
        description: 'Returns a random characters from the indexed catalog. Outcome varies per request.',
        tags: ['random'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns a random character resource',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/character'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function characters(QueryRandomCharacterCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/random/people',
        operationId: 'getRandomPeople',
        summary: 'Random people',
        description: 'Returns a random people from the indexed catalog. Outcome varies per request.',
        tags: ['random'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns a random person resource',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/person'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function people(QueryRandomPersonCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/random/users',
        operationId: 'getRandomUsers',
        summary: 'Random users',
        description: 'Returns a random users from the indexed catalog. Outcome varies per request.',
        tags: ['random'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns a random user profile resource',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/user_profile'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function users(QueryRandomUserCommand $command): mixed
    {
        return $this->mediator->send($command);
    }
}
