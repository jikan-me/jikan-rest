<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'manga_statistics',
    description: 'Manga Statistics Resource',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'object',
            properties: [
                new OA\Property(property: 'reading', type: 'integer', description: 'Number of users reading the resource'),
                new OA\Property(property: 'completed', type: 'integer', description: 'Number of users who have completed the resource'),
                new OA\Property(property: 'on_hold', type: 'integer', description: 'Number of users who have put the resource on hold'),
                new OA\Property(property: 'dropped', type: 'integer', description: 'Number of users who have dropped the resource'),
                new OA\Property(property: 'plan_to_read', type: 'integer', description: 'Number of users who have planned to read the resource'),
                new OA\Property(property: 'total', type: 'integer', description: 'Total number of users who have the resource added to their lists'),
                new OA\Property(
                    property: 'scores',
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'score', type: 'integer', description: 'Scoring value'),
                            new OA\Property(property: 'votes', type: 'integer', description: 'Number of votes for this score'),
                            new OA\Property(property: 'percentage', type: 'number', format: 'float', description: 'Percentage of votes for this score'),
                        ],
                    ),
                ),
            ],
        ),
    ],
)]
class MangaStatisticsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    /**
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'reading' => $this['reading'],
            'completed' => $this['completed'],
            'on_hold' => $this['on_hold'],
            'dropped' => $this['dropped'],
            'plan_to_read' => $this['plan_to_read'],
            'total' => $this['total'],
            'scores' => $this['scores'],
        ];
    }
}
