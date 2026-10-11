<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\PersonAnimeLookupCommand;
use App\Dto\PersonFullLookupCommand;
use App\Dto\PersonLookupCommand;
use App\Dto\PersonMangaLookupCommand;
use App\Dto\PersonPicturesLookupCommand;
use App\Dto\PersonVoicesLookupCommand;
use OpenApi\Attributes as OA;

class PersonController extends Controller
{
    #[OA\Get(
        path: '/people/{id}/full',
        operationId: 'getPersonFullById',
        summary: 'Complete person resource',
        description: 'Returns the complete person resource — primary data plus related sub-resources — in one response for the given **id**.',
        tags: ['people'],
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
                        new OA\Property(property: 'data', ref: '#/components/schemas/person_full'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function full(PersonFullLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/people/{id}',
        operationId: 'getPersonById',
        summary: 'Person details',
        description: 'Retrieves a particular person for the given **id**.',
        tags: ['people'],
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
                        new OA\Property(property: 'data', ref: '#/components/schemas/person'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function main(PersonLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/people/{id}/anime',
        operationId: 'getPersonAnime',
        summary: 'Person anime roles',
        description: 'Retrieves the person anime roles for the person identified by **id**.',
        tags: ['people'],
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
                description: 'Returns person\'s anime staff positions',
                content: new OA\JsonContent(ref: '#/components/schemas/person_anime'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function anime(PersonAnimeLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/people/{id}/voices',
        operationId: 'getPersonVoices',
        summary: 'Person voice acting roles',
        description: 'Retrieves the person voice acting roles for the person identified by **id**.',
        tags: ['people'],
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
                description: 'Returns person\'s voice acting roles',
                content: new OA\JsonContent(ref: '#/components/schemas/person_voice_acting_roles'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function voices(PersonVoicesLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/people/{id}/manga',
        operationId: 'getPersonManga',
        summary: 'Person manga roles',
        description: 'Retrieves the person manga roles for the person identified by **id**.',
        tags: ['people'],
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
                description: 'Returns person\'s published manga works',
                content: new OA\JsonContent(ref: '#/components/schemas/person_manga'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function manga(PersonMangaLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/people/{id}/pictures',
        operationId: 'getPersonPictures',
        summary: 'Person pictures',
        description: 'Retrieves the person pictures for the person identified by **id**.',
        tags: ['people'],
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
                description: 'Returns a list of pictures of the person',
                content: new OA\JsonContent(ref: '#/components/schemas/person_pictures'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    
    #[OA\Schema(
        schema: 'person_pictures',
        description: 'Character Pictures',
        properties: [
            new OA\Property(
                property: 'data',
                type: 'array',
                items: new OA\Items(type: 'object', ref: '#/components/schemas/people_images'),
            ),
        ],
    )]
    public function pictures(PersonPicturesLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }
}
