<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\QueryAnimeSchedulesCommand;
use OpenApi\Attributes as OA;

class ScheduleController extends Controller
{
    #[OA\Get(
        path: '/schedules',
        operationId: 'getSchedules',
        summary: 'Anime schedule',
        description: 'Returns the weekly airing schedule of anime. Use **filter** to select a weekday, with **kids** and **sfw** to include or exclude entries by their ratings, and pagination via **page**.',
        tags: ['schedules'],
        parameters: [
            new OA\Parameter(
                name: 'filter',
                in: 'query',
                required: false,
                description: 'Filter by day',
                schema: new OA\Schema(type: 'string', enum: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'unknown', 'other']),
            ),
            new OA\Parameter(
                name: 'kids',
                in: 'query',
                required: false,
                description: 'When supplied, it will filter entries with the `Kids` Genre Demographic. When supplied as `kids=true`, it will return only Kid entries and when supplied as `kids=false`, it will filter out any Kid entries. Defaults to `false`.',
                schema: new OA\Schema(type: 'string', enum: ['true', 'false']),
            ),
            new OA\Parameter(
                name: 'sfw',
                in: 'query',
                required: false,
                description: '\'Safe For Work\'. When supplied, it will filter entries with the `Hentai` Genre. When supplied as `sfw=true`, it will return only SFW entries and when supplied as `sfw=false`, it will filter out any Hentai entries. Defaults to `false`.',
                schema: new OA\Schema(type: 'string', enum: ['true', 'false']),
            ),
            new OA\Parameter(ref: '#/components/parameters/unapproved'),
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns weekly schedule',
                content: new OA\JsonContent(ref: '#/components/schemas/schedules'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]

    #[OA\Schema(schema: 'schedules', description: 'Anime resources currently airing', allOf: [new OA\Schema(ref: '#/components/schemas/pagination_plus'), new OA\Schema(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object', ref: '#/components/schemas/anime'))])])]
    public function main(QueryAnimeSchedulesCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    //    public function byDay(QueryAnimeSchedulesByDayCommand $command)
    //    {
    //        return $this->mediator->send($command);
    //    }
}
