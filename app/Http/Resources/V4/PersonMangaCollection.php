<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'person_manga',
    description: 'Person\'s mangaography',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'position', type: 'string', description: 'Person\'s position'),
                    new OA\Property(property: 'manga', type: 'object', ref: '#/components/schemas/manga_meta'),
                ],
            ),
        ),
    ],
)]
class PersonMangaCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     */
    public $collects = 'App\Http\Resources\V4\PersonMangaResource';

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
