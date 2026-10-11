<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'manga_review',
    type: 'object',
    properties: [
        new OA\Property(property: 'mal_id', type: 'integer', description: 'MyAnimeList ID'),
        new OA\Property(property: 'url', type: 'string', description: 'MyAnimeList review URL'),
        new OA\Property(property: 'type', type: 'string', description: 'Entry type'),
        new OA\Property(
            property: 'reactions',
            type: 'object',
            description: 'User reaction count on the review',
            properties: [
                new OA\Property(property: 'overall', type: 'integer', description: 'Overall reaction count'),
                new OA\Property(property: 'nice', type: 'integer', description: 'Nice reaction count'),
                new OA\Property(property: 'love_it', type: 'integer', description: 'Love it reaction count'),
                new OA\Property(property: 'funny', type: 'integer', description: 'Funny reaction count'),
                new OA\Property(property: 'confusing', type: 'integer', description: 'Confusing reaction count'),
                new OA\Property(property: 'informative', type: 'integer', description: 'Informative reaction count'),
                new OA\Property(property: 'well_written', type: 'integer', description: 'Well written reaction count'),
                new OA\Property(property: 'creative', type: 'integer', description: 'Creative reaction count'),
            ],
        ),
        new OA\Property(property: 'date', type: 'string', description: 'Review created date ISO8601'),
        new OA\Property(property: 'review', type: 'string', description: 'Review content'),
        new OA\Property(property: 'score', type: 'integer', description: 'Number of user votes on the Review'),
        new OA\Property(
            property: 'tags',
            type: 'array',
            description: 'Review tags',
            items: new OA\Items(type: 'string'),
        ),
        new OA\Property(property: 'is_spoiler', type: 'boolean', description: 'The review contains spoiler'),
        new OA\Property(property: 'is_preliminary', type: 'boolean', description: 'The review was made before the entry was completed'),
    ],
)]

#[OA\Schema(
    schema: 'anime_review',
    type: 'object',
    properties: [
        new OA\Property(property: 'mal_id', type: 'integer', description: 'MyAnimeList ID'),
        new OA\Property(property: 'url', type: 'string', description: 'MyAnimeList review URL'),
        new OA\Property(property: 'type', type: 'string', description: 'Entry type'),
        new OA\Property(
            property: 'reactions',
            type: 'object',
            description: 'User reaction count on the review',
            properties: [
                new OA\Property(property: 'overall', type: 'integer', description: 'Overall reaction count'),
                new OA\Property(property: 'nice', type: 'integer', description: 'Nice reaction count'),
                new OA\Property(property: 'love_it', type: 'integer', description: 'Love it reaction count'),
                new OA\Property(property: 'funny', type: 'integer', description: 'Funny reaction count'),
                new OA\Property(property: 'confusing', type: 'integer', description: 'Confusing reaction count'),
                new OA\Property(property: 'informative', type: 'integer', description: 'Informative reaction count'),
                new OA\Property(property: 'well_written', type: 'integer', description: 'Well written reaction count'),
                new OA\Property(property: 'creative', type: 'integer', description: 'Creative reaction count'),
            ],
        ),
        new OA\Property(property: 'date', type: 'string', description: 'Review created date ISO8601'),
        new OA\Property(property: 'review', type: 'string', description: 'Review content'),
        new OA\Property(property: 'score', type: 'integer', description: 'Number of user votes on the Review'),
        new OA\Property(
            property: 'tags',
            type: 'array',
            description: 'Review tags',
            items: new OA\Items(type: 'string'),
        ),
        new OA\Property(property: 'is_spoiler', type: 'boolean', description: 'The review contains spoiler'),
        new OA\Property(property: 'is_preliminary', type: 'boolean', description: 'The review was made before the entry was completed'),
        new OA\Property(property: 'episodes_watched', type: 'integer', description: 'Number of episodes watched'),
    ],
)]

#[OA\Schema(schema: 'anime_reviews', description: 'Anime Reviews Resource', allOf: [new OA\Schema(ref: '#/components/schemas/pagination'), new OA\Schema(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object', allOf: [new OA\Schema(ref: '#/components/schemas/anime_review'), new OA\Schema(properties: [new OA\Property(property: 'user', type: 'object', ref: '#/components/schemas/user_meta')])]))])])]

#[OA\Schema(
    schema: 'manga_reviews',
    description: 'Manga Reviews Resource',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/pagination'),
        new OA\Schema(
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(type: 'object', allOf: [new OA\Schema(ref: '#/components/schemas/manga_review'), new OA\Schema(properties: [new OA\Property(property: 'user', type: 'object', ref: '#/components/schemas/user_meta')])]),
                ),
            ],
        ),
    ],
)]
class ReviewsResource extends JsonResource
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
            'pagination' => [
                'last_visible_page' => $this['last_visible_page'],
                'has_next_page' => $this['has_next_page'],
            ],
            'data' => $this['results'],
        ];
    }
}
