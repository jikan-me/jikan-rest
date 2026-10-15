<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'anime_userupdates', description: 'Anime User Updates Resource', allOf: [new OA\Schema(ref: '#/components/schemas/pagination'), new OA\Schema(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object', properties: [new OA\Property(property: 'user', type: 'object', ref: '#/components/schemas/user_meta'), new OA\Property(property: 'score', type: 'integer', description: 'User Score', nullable: true), new OA\Property(property: 'status', type: 'string', description: 'User list status'), new OA\Property(property: 'episodes_seen', type: 'integer', description: 'Number of episodes seen', nullable: true), new OA\Property(property: 'episodes_total', type: 'integer', description: 'Total number of episodes', nullable: true), new OA\Property(property: 'date', type: 'string', description: 'Last updated date ISO8601')]))])])]

#[OA\Schema(schema: 'manga_userupdates', description: 'Manga User Updates Resource', allOf: [new OA\Schema(ref: '#/components/schemas/pagination'), new OA\Schema(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object', properties: [new OA\Property(property: 'user', type: 'object', ref: '#/components/schemas/user_meta'), new OA\Property(property: 'score', type: 'integer', description: 'User Score', nullable: true), new OA\Property(property: 'status', type: 'string', description: 'User list status'), new OA\Property(property: 'volumes_read', type: 'integer', description: 'Number of volumes read', nullable: true), new OA\Property(property: 'volumes_total', type: 'integer', description: 'Total number of volumes', nullable: true), new OA\Property(property: 'chapters_read', type: 'integer', description: 'Number of chapters read', nullable: true), new OA\Property(property: 'chapters_total', type: 'integer', description: 'Total number of chapters', nullable: true), new OA\Property(property: 'date', type: 'string', description: 'Last updated date ISO8601')]))])])]
class UserUpdatesResource extends JsonResource
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
        return $this['users'];
    }
}
