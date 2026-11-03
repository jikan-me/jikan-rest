<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'user_history',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(type: 'object', ref: '#/components/schemas/history'),
        ),
    ],
)]

#[OA\Schema(
    schema: 'history',
    type: 'object',
    properties: [
        new OA\Property(property: 'entry', type: 'object', description: 'Entry Meta', ref: '#/components/schemas/mal_url'),
        new OA\Property(property: 'increment', type: 'integer', description: 'Number of episodes/chapters watched/read'),
        new OA\Property(property: 'date', type: 'string', description: 'Date ISO8601'),
    ],
)]
class ProfileHistoryResource extends JsonResource
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
        return $this['history'];
    }
}
