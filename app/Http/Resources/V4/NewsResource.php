<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'news',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'mal_id', type: 'integer', description: 'MyAnimeList ID'),
                    new OA\Property(property: 'url', type: 'string', description: 'MyAnimeList URL'),
                    new OA\Property(property: 'title', type: 'string', description: 'Title'),
                    new OA\Property(property: 'date', type: 'string', description: 'Post Date ISO8601'),
                    new OA\Property(property: 'author_username', type: 'string', description: 'Author MyAnimeList Username'),
                    new OA\Property(property: 'author_url', type: 'string', description: 'Author Profile URL'),
                    new OA\Property(property: 'forum_url', type: 'string', description: 'Forum topic URL'),
                    new OA\Property(property: 'images', type: 'object', ref: '#/components/schemas/common_images'),
                    new OA\Property(property: 'comments', type: 'integer', description: 'Comment count'),
                    new OA\Property(property: 'excerpt', type: 'string', description: 'Excerpt'),
                ],
            ),
        ),
    ],
)]
class NewsResource extends JsonResource
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
                'last_visible_page' => $this['last_visible_page'] ?? 1,
                'has_next_page' => $this['has_next_page'] ?? false,
            ],
            'data' => $this['results'],
        ];
    }
}
