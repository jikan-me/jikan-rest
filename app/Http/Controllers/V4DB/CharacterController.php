<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Character;
use App\Dto\CharacterAnimeLookupCommand;
use App\Dto\CharacterFullLookupCommand;
use App\Dto\CharacterLookupCommand;
use App\Dto\CharacterMangaLookupCommand;
use App\Dto\CharacterPicturesLookupCommand;
use App\Dto\CharacterVoicesLookupCommand;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CharacterController extends Controller
{
    #[OA\Get(
        path: '/characters/{id}/full',
        operationId: 'getCharacterFullById',
        summary: 'Complete character resource',
        description: 'Returns the complete character resource — primary data plus related sub-resources — in one response for the given **id**.',
        tags: ['characters'],
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
                description: 'Returns complete character resource data',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/character_full'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function full(CharacterFullLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/characters/{id}',
        operationId: 'getCharacterById',
        summary: 'Character details',
        description: 'Retrieves a particular character for the given **id**.',
        tags: ['characters'],
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
                description: 'Returns character resource',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/character'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function main(CharacterLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/characters/{id}/anime',
        operationId: 'getCharacterAnime',
        summary: 'Character anime roles',
        description: 'Retrieves the character anime roles for the character identified by **id**.',
        tags: ['characters'],
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
                description: 'Returns anime that character is in',
                content: new OA\JsonContent(ref: '#/components/schemas/character_anime'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function anime(CharacterAnimeLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/characters/{id}/manga',
        operationId: 'getCharacterManga',
        summary: 'Character manga roles',
        description: 'Retrieves the character manga roles for the character identified by **id**.',
        tags: ['characters'],
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
                description: 'Returns manga that character is in',
                content: new OA\JsonContent(ref: '#/components/schemas/character_manga'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function manga(CharacterMangaLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/characters/{id}/voices',
        operationId: 'getCharacterVoiceActors',
        summary: 'Character voice actors',
        description: 'Retrieves the character voice actors for the character identified by **id**.',
        tags: ['characters'],
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
                description: 'Returns the character\'s voice actors',
                content: new OA\JsonContent(ref: '#/components/schemas/character_voice_actors'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function voices(CharacterVoicesLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/characters/{id}/pictures',
        operationId: 'getCharacterPictures',
        summary: 'Character pictures',
        description: 'Retrieves the character pictures for the character identified by **id**.',
        tags: ['characters'],
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
                content: new OA\JsonContent(ref: '#/components/schemas/character_pictures'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    
    #[OA\Schema(
        schema: 'character_pictures',
        description: 'Character Pictures',
        properties: [
            new OA\Property(
                property: 'data',
                type: 'array',
                items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'image_url', type: 'string', description: 'Default JPG Image Size URL', nullable: true),
                        new OA\Property(property: 'large_image_url', type: 'string', description: 'Large JPG Image Size URL', nullable: true),
                    ],
                ),
            ),
        ],
    )]
    public function pictures(CharacterPicturesLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }
}
