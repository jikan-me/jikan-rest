<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'forum',
    description: 'Forum Resource',
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
                    new OA\Property(property: 'comments', type: 'integer', description: 'Comment count'),
                    new OA\Property(
                        property: 'last_comment',
                        type: 'object',
                        description: 'Last comment details',
                        properties: [
                            new OA\Property(property: 'url', type: 'string', description: 'Last comment URL'),
                            new OA\Property(property: 'author_username', type: 'string', description: 'Author MyAnimeList Username'),
                            new OA\Property(property: 'author_url', type: 'string', description: 'Author Profile URL'),
                            new OA\Property(property: 'date', type: 'string', description: 'Last comment date posted ISO8601', nullable: true),
                        ],
                    ),
                ],
            ),
        ),
    ],
)]
class ForumResource extends JsonResource
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
        return $this['topics'];
    }
}
