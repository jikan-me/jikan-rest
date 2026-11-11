<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'character_manga',
    description: 'Character casted in manga',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'role', type: 'string', description: 'Character\'s Role'),
                    new OA\Property(property: 'manga', type: 'object', ref: '#/components/schemas/manga_meta'),
                ],
            ),
        ),
    ],
)]
class CharacterMangaCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     */
    public $collects = 'App\Http\Resources\V4\CharacterMangaResource';

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
