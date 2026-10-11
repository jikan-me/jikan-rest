<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\ProducerExternalLookupCommand;
use App\Dto\ProducerFullLookupCommand;
use App\Dto\ProducerLookupCommand;
use OpenApi\Attributes as OA;

class ProducerController extends Controller
{
    #[OA\Get(
        path: '/producers/{id}',
        operationId: 'getProducerById',
        summary: 'Producer details',
        description: 'Retrieves the producer details for the producer identified by **id**.',
        tags: ['producers'],
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
                description: 'Returns producer resource',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/producer'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function main(ProducerLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/producers/{id}/full',
        operationId: 'getProducerFullById',
        summary: 'Complete producer resource',
        description: 'Returns the complete producer resource — primary data plus related sub-resources — in one response for the given **id**.',
        tags: ['producers'],
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
                description: 'Returns producer resource',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/producer_full'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function full(ProducerFullLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/producers/{id}/external',
        operationId: 'getProducerExternal',
        summary: 'Producer external links',
        description: 'Retrieves the producer external links for a producer.',
        tags: ['producers'],
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
                description: 'Returns producer\'s external links',
                content: new OA\JsonContent(ref: '#/components/schemas/external_links'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function external(ProducerExternalLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }
}
