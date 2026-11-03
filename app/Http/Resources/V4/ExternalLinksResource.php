<?php

namespace App\Http\Resources\V4;

use App\Support\CachedData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin CachedData */
#[OA\Schema(
    schema: 'external_links',
    description: 'External links',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'url', type: 'string'),
                ],
            ),
        ),
    ],
)]
class ExternalLinksResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return $this->external_links;
    }
}
