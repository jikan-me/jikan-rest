<?php

namespace App\Http\Resources\V4;

use App\Magazine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin Magazine */
#[OA\Schema(
    schema: 'magazine',
    description: 'Magazine Resource',
    properties: [
        new OA\Property(property: 'mal_id', type: 'integer', description: 'MyAnimeList ID', nullable: true),
        new OA\Property(property: 'name', type: 'string', description: 'Magazine Name'),
        new OA\Property(property: 'url', type: 'string', description: 'MyAnimeList URL'),
        new OA\Property(property: 'count', type: 'integer', description: 'Magazine\'s manga count'),
    ],
)]
class MagazineResource extends JsonResource
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
            'name' => $this->name,
            'url' => $this->url,
            'count' => $this->count,
        ];
    }
}
