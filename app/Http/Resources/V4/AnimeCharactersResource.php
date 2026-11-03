<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'anime_characters',
    description: 'Anime Characters Resource',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'character',
                        type: 'object',
                        description: 'Character details',
                        properties: [
                            new OA\Property(property: 'mal_id', type: 'integer', description: 'MyAnimeList ID'),
                            new OA\Property(property: 'url', type: 'string', description: 'MyAnimeList URL'),
                            new OA\Property(property: 'images', type: 'object', ref: '#/components/schemas/character_images'),
                            new OA\Property(property: 'name', type: 'string', description: 'Character Name'),
                        ],
                    ),
                    new OA\Property(property: 'role', type: 'string', description: 'Character\'s Role'),
                    new OA\Property(property: 'favorites', type: 'integer', description: 'Number of users who have favorited this character'),
                    new OA\Property(
                        property: 'voice_actors',
                        type: 'array',
                        items: new OA\Items(
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'person',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'mal_id', type: 'integer'),
                                        new OA\Property(property: 'url', type: 'string'),
                                        new OA\Property(property: 'images', type: 'object', ref: '#/components/schemas/people_images'),
                                        new OA\Property(property: 'name', type: 'string'),
                                    ],
                                ),
                                new OA\Property(property: 'language', type: 'string'),
                            ],
                        ),
                    ),
                ],
            ),
        ),
    ],
)]
class AnimeCharactersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return $this['characters'];
    }
}
