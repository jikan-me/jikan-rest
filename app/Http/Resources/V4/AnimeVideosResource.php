<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'anime_videos',
    description: 'Anime Videos Resource',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'promo',
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'title', type: 'string', description: 'Title'),
                            new OA\Property(property: 'trailer', ref: '#/components/schemas/trailer'),
                        ],
                    ),
                ),
                new OA\Property(
                    property: 'episodes',
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'mal_id', type: 'integer', description: 'MyAnimeList ID'),
                            new OA\Property(property: 'url', type: 'string', description: 'MyAnimeList URL'),
                            new OA\Property(property: 'title', type: 'string', description: 'Title'),
                            new OA\Property(property: 'episode', type: 'string', description: 'Episode'),
                            new OA\Property(property: 'images', type: 'object', ref: '#/components/schemas/common_images'),
                        ],
                    ),
                ),
                new OA\Property(
                    property: 'music_videos',
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'title', type: 'string', description: 'Title'),
                            new OA\Property(property: 'video', ref: '#/components/schemas/trailer'),
                            new OA\Property(
                                type: 'object',
                                property: 'meta',
                                properties: [
                                    new OA\Property(property: 'title', type: 'string', nullable: true),
                                    new OA\Property(property: 'author', type: 'string', nullable: true),
                                ],
                            ),
                        ],
                    ),
                ),
            ],
        ),
    ],
)]
class AnimeVideosResource extends JsonResource
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
            'promo' => $this['promo'],
            'episodes' => $this['episodes'],
            'music_videos' => $this['music_videos'],
        ];
    }
}
