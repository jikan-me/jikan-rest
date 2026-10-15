<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'club_relations',
    description: 'Club Relations',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'anime',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/mal_url'),
                ),
                new OA\Property(
                    property: 'manga',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/mal_url'),
                ),
                new OA\Property(
                    property: 'characters',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/mal_url'),
                ),
            ],
        ),
    ],
)]
class ClubRelationsResource extends JsonResource
{


    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'anime' => $this['anime'],
            'manga' => $this['manga'],
            'characters' => $this['characters'],
        ];
    }
}
