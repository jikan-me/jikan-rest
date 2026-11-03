<?php

namespace App\Http\Resources\V4;

use App\Club;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin Club */
#[OA\Schema(
    schema: 'club',
    description: 'Club Resource',
    properties: [
        new OA\Property(property: 'mal_id', type: 'integer', description: 'MyAnimeList ID'),
        new OA\Property(property: 'name', type: 'string', description: 'Club name'),
        new OA\Property(property: 'url', type: 'string', description: 'Club URL'),
        new OA\Property(property: 'images', type: 'object', ref: '#/components/schemas/common_images'),
        new OA\Property(property: 'members', type: 'integer', description: 'Number of club members'),
        new OA\Property(property: 'category', type: 'string', description: 'Club Category', enum: ['actors & artists', 'anime', 'characters', 'cities & neighborhoods', 'companies', 'conventions', 'games', 'japan', 'manga', 'music', 'others', 'schools']),
        new OA\Property(property: 'created', type: 'string', description: 'Date Created ISO8601'),
        new OA\Property(property: 'access', type: 'string', description: 'Club access', enum: ['public', 'private', 'secret']),
    ],
)]
class ClubResource extends JsonResource
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
            'images' => $this->images,
            'name' => $this->name,
            'members' => $this->members,
            'category' => $this->category,
            'created' => $this->created,
            'access' => $this->access,
        ];
    }
}
