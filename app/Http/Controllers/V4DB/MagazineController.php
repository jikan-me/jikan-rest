<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\MagazineSearchCommand;
use App\Http\Resources\V4\MagazineCollection;
use OpenApi\Attributes as OA;

class MagazineController extends Controller
{
    #[OA\Get(
        path: '/magazines',
        operationId: 'getMagazines',
        summary: 'Magazines list',
        description: 'Searches the magazines catalog using the **q**, **order_by**, **sort**, and **letter** filters. Supports pagination via **page**.',
        tags: ['magazines'],
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
                schema: new OA\Schema(ref: '#/components/schemas/magazines_query_orderby'),
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
                description: 'Returns magazines collection',
                content: new OA\JsonContent(ref: '#/components/schemas/magazines'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function main(MagazineSearchCommand $command): MagazineCollection
    {
        return $this->mediator->send($command);
    }
}
