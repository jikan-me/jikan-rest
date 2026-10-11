<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'manga_characters',
    description: 'Manga Characters Resource',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'character', type: 'object', ref: '#/components/schemas/character_meta'),
                    new OA\Property(property: 'role', type: 'string', description: 'Character\'s Role'),
                ],
            ),
        ),
    ],
)]
class MangaCharactersResource extends JsonResource
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
        return $this['characters'];
    }
}
