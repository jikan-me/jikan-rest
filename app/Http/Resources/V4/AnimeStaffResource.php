<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'anime_staff',
    description: 'Anime Staff Resource',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'person',
                        type: 'object',
                        description: 'Person details',
                        properties: [
                            new OA\Property(property: 'mal_id', type: 'integer', description: 'MyAnimeList ID'),
                            new OA\Property(property: 'url', type: 'string', description: 'MyAnimeList URL'),
                            new OA\Property(property: 'images', type: 'string', ref: '#/components/schemas/people_images'),
                            new OA\Property(property: 'name', type: 'string', description: 'Name'),
                        ],
                    ),
                    new OA\Property(
                        property: 'positions',
                        type: 'array',
                        description: 'Staff Positions',
                        items: new OA\Items(type: 'string'),
                    ),
                ],
            ),
        ),
    ],
)]
class AnimeStaffResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return $this['staff'];
    }
}
