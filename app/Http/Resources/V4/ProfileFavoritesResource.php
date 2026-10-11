<?php

namespace App\Http\Resources\V4;

use App\Profile;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin Profile */
#[OA\Schema(
    schema: 'user_favorites',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'anime',
            type: 'array',
            description: 'Favorite Anime',
            items: new OA\Items(type: 'object', allOf: [new OA\Schema(ref: '#/components/schemas/anime_meta'), new OA\Schema(properties: [new OA\Property(property: 'type', type: 'string'), new OA\Property(property: 'start_year', type: 'integer')])]),
        ),
        new OA\Property(
            property: 'manga',
            type: 'array',
            description: 'Favorite Manga',
            items: new OA\Items(type: 'object', allOf: [new OA\Schema(ref: '#/components/schemas/manga_meta'), new OA\Schema(properties: [new OA\Property(property: 'type', type: 'string'), new OA\Property(property: 'start_year', type: 'integer')])]),
        ),
        new OA\Property(
            property: 'characters',
            type: 'array',
            description: 'Favorite Characters',
            items: new OA\Items(type: 'object', allOf: [new OA\Schema(ref: '#/components/schemas/character_meta'), new OA\Schema(ref: '#/components/schemas/mal_url_2')]),
        ),
        new OA\Property(
            property: 'people',
            type: 'array',
            description: 'Favorite People',
            items: new OA\Items(ref: '#/components/schemas/character_meta'),
        ),
    ],
)]
class ProfileFavoritesResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable|mixed
     */
    public function toArray($request)
    {
        return $this->favorites;
    }
}
