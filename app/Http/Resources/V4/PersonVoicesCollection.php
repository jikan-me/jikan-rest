<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'person_voice_acting_roles',
    description: 'Person\'s voice acting roles',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'role', type: 'string', description: 'Person\'s Character\'s role in the anime'),
                    new OA\Property(property: 'anime', type: 'object', description: 'The anime in which the person is voice acting in', ref: '#/components/schemas/anime_meta'),
                    new OA\Property(property: 'character', type: 'object', description: 'The character the person is voice acting for', ref: '#/components/schemas/character_meta'),
                ],
            ),
        ),
    ],
)]
class PersonVoicesCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     */
    public $collects = 'App\Http\Resources\V4\PersonVoiceResource';

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
