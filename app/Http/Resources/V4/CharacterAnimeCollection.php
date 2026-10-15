<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'character_anime',
    description: 'Character casted in anime',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'role', type: 'string', description: 'Character\'s Role'),
                    new OA\Property(property: 'anime', type: 'object', ref: '#/components/schemas/anime_meta'),
                ],
            ),
        ),
    ],
)]
class CharacterAnimeCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     */
    public $collects = 'App\Http\Resources\V4\CharacterAnimeResource';

    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
        ];
    }
}
