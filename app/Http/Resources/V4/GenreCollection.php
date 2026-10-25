<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'genres', type: 'object', description: 'Genres Collection Resource', properties: [
    new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object', ref: '#/components/schemas/genre')),
])]
class GenreCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = 'App\Http\Resources\V4\GenreResource';
}
