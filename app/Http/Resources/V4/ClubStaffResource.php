<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'club_staff',
    description: 'Club Staff Resource',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'url', type: 'string', description: 'User URL'),
                    new OA\Property(property: 'username', type: 'string', description: 'User\'s username'),
                ],
            ),
        ),
    ],
)]
class ClubStaffResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return $this['staff'];
    }
}
