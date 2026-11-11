<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'pictures',
    description: 'Pictures Resource',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'images', ref: '#/components/schemas/anime_images'),
                ],
            ),
        ),
    ],
)]

#[OA\Schema(
    schema: 'pictures_variants',
    description: 'Pictures Resource',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'images', ref: '#/components/schemas/common_images'),
                ],
            ),
        ),
    ],
)]
class PicturesResource extends JsonResource
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
        return $this['pictures'];
    }
}
