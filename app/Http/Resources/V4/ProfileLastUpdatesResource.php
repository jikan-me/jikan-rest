<?php

namespace App\Http\Resources\V4;

use App\Profile;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin Profile */
#[OA\Schema(
    schema: 'user_updates',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'anime',
                    type: 'array',
                    description: 'Last updated Anime',
                    items: new OA\Items(type: 'object', allOf: [new OA\Schema(properties: [new OA\Property(property: 'entry', type: 'object', ref: '#/components/schemas/anime_meta')]), new OA\Schema(properties: [new OA\Property(property: 'score', type: 'integer', nullable: true), new OA\Property(property: 'status', type: 'string'), new OA\Property(property: 'episodes_seen', type: 'integer', nullable: true), new OA\Property(property: 'episodes_total', type: 'integer', nullable: true), new OA\Property(property: 'date', description: 'ISO8601 format', type: 'string')])]),
                ),
                new OA\Property(
                    property: 'manga',
                    type: 'array',
                    description: 'Last updated Manga',
                    items: new OA\Items(type: 'object', allOf: [new OA\Schema(properties: [new OA\Property(property: 'entry', type: 'object', ref: '#/components/schemas/manga_meta')]), new OA\Schema(properties: [new OA\Property(property: 'score', type: 'integer', nullable: true), new OA\Property(property: 'status', type: 'string'), new OA\Property(property: 'chapters_read', type: 'integer', nullable: true), new OA\Property(property: 'chapters_total', type: 'integer', nullable: true), new OA\Property(property: 'volumes_read', type: 'integer', nullable: true), new OA\Property(property: 'volumes_total', type: 'integer', nullable: true), new OA\Property(property: 'date', description: 'ISO8601 format', type: 'string')])]),
                ),
            ],
        ),
    ],
)]
class ProfileLastUpdatesResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable|mixed
     */
    public function toArray($request)
    {
        return $this->last_updates;
    }
}
