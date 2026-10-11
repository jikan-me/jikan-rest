<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'anime_relations',
    description: 'Anime Relations',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'relation', type: 'string', description: 'Relation type'),
                    new OA\Property(
                        property: 'entry',
                        type: 'array',
                        items: new OA\Items(type: 'object', ref: '#/components/schemas/mal_url'),
                    ),
                ],
            ),
        ),
    ],
)]
class AnimeRelationsResource extends JsonResource
{


    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return $this['related'];
    }
}
