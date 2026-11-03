<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'anime_themes',
    description: 'Anime Opening and Ending Themes',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'openings',
                    type: 'array',
                    items: new OA\Items(type: 'string'),
                ),
                new OA\Property(
                    property: 'endings',
                    type: 'array',
                    items: new OA\Items(type: 'string'),
                ),
            ],
        ),
    ],
)]
class AnimeThemesResource extends JsonResource
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
            'openings' => $this->opening_themes ?? [],
            'endings' => $this->ending_themes ?? [],
        ];
    }
}
