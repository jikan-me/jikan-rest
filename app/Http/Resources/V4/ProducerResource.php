<?php

namespace App\Http\Resources\V4;

use App\Producers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin Producers */
#[OA\Schema(
    schema: 'producer',
    description: 'Producers Resource',
    properties: [
        new OA\Property(property: 'mal_id', type: 'integer', description: 'MyAnimeList ID'),
        new OA\Property(property: 'url', type: 'string', description: 'MyAnimeList URL'),
        new OA\Property(
            property: 'titles',
            type: 'array',
            description: 'All titles',
            items: new OA\Items(type: 'object', ref: '#/components/schemas/title'),
        ),
        new OA\Property(property: 'images', type: 'object', ref: '#/components/schemas/common_images'),
        new OA\Property(property: 'favorites', type: 'integer', description: 'Producers\'s member favorites count'),
        new OA\Property(property: 'count', type: 'integer', description: 'Producers\'s anime count'),
        new OA\Property(property: 'established', type: 'string', description: 'Established Date ISO8601', nullable: true),
        new OA\Property(property: 'about', type: 'string', description: 'About the Producer', nullable: true),
    ],
)]
class ProducerResource extends JsonResource
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
        return [
            'mal_id' => $this->mal_id,
            'url' => $this->url,
            'titles' => $this->titles,
            'images' => $this->images,
            'favorites' => $this->favorites ?? null,
            'established' => $this->established ?? null,
            'about' => $this->about ?? null,
            'count' => $this->count ?? null,
        ];
    }
}
