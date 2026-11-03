<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'recommendations', description: 'Recommendations', allOf: [new OA\Schema(ref: '#/components/schemas/pagination'), new OA\Schema(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object', properties: [new OA\Property(property: 'mal_id', type: 'string', description: 'MAL IDs of recommendations is both of the MAL ID\'s with a `-` delimiter'), new OA\Property(property: 'entry', type: 'array', description: 'Array of 2 entries that are being recommended to each other', items: new OA\Items(type: 'object', anyOf: [new OA\Schema(ref: '#/components/schemas/anime_meta'), new OA\Schema(ref: '#/components/schemas/manga_meta')])), new OA\Property(property: 'content', type: 'string', description: 'Recommendation context provided by the user'), new OA\Property(property: 'user', type: 'object', ref: '#/components/schemas/user_by_id')]))])])]

#[OA\Schema(
    schema: 'entry_recommendations',
    description: 'Entry Recommendations Resource',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'entry', type: 'object', oneOf: [new OA\Schema(ref: '#/components/schemas/anime_meta'), new OA\Schema(ref: '#/components/schemas/manga_meta')]),
                ],
            ),
            properties: [
                new OA\Property(property: 'url', type: 'string', description: 'Recommendation MyAnimeList URL'),
                new OA\Property(property: 'votes', type: 'integer', description: 'Number of users who have recommended this entry'),
            ],
        ),
    ],
)]
class RecommendationsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * )
     */
    /**
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return $this['recommendations'];
    }
}
