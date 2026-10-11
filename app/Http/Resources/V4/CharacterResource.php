<?php

namespace App\Http\Resources\V4;

use App\Character;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin Character */
#[OA\Schema(
    schema: 'character',
    description: 'Character Resource',
    properties: [
        new OA\Property(property: 'mal_id', type: 'integer', description: 'MyAnimeList ID'),
        new OA\Property(property: 'url', type: 'string', description: 'MyAnimeList URL'),
        new OA\Property(property: 'images', ref: '#/components/schemas/character_images'),
        new OA\Property(property: 'name', type: 'string', description: 'Name'),
        new OA\Property(property: 'name_kanji', type: 'string', description: 'Name', nullable: true),
        new OA\Property(
            property: 'nicknames',
            type: 'array',
            description: 'Other Names',
            items: new OA\Items(type: 'string'),
        ),
        new OA\Property(property: 'favorites', type: 'integer', description: 'Number of users who have favorited this entry'),
        new OA\Property(property: 'about', type: 'string', description: 'Biography', nullable: true),
    ],
)]
class CharacterResource extends JsonResource
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
            'mal_id' => $this->mal_id,
            'url' => $this->url,
            'images' => $this->images,
            'name' => $this->name,
            'name_kanji' => $this->name_kanji,
            'nicknames' => $this->nicknames,
            'favorites' => $this->favorites,
            'about' => $this->about,
        ];
    }
}
