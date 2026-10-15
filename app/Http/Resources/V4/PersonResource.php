<?php

namespace App\Http\Resources\V4;

use App\Person;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin Person */
#[OA\Schema(
    schema: 'person',
    description: 'Person Resource',
    properties: [
        new OA\Property(property: 'mal_id', type: 'integer', description: 'MyAnimeList ID'),
        new OA\Property(property: 'url', type: 'string', description: 'MyAnimeList URL'),
        new OA\Property(property: 'website_url', type: 'string', description: 'Person\'s website URL', nullable: true),
        new OA\Property(property: 'images', type: 'object', ref: '#/components/schemas/people_images'),
        new OA\Property(property: 'name', type: 'string', description: 'Name'),
        new OA\Property(property: 'given_name', type: 'string', description: 'Given Name', nullable: true),
        new OA\Property(property: 'family_name', type: 'string', description: 'Family Name', nullable: true),
        new OA\Property(
            property: 'alternate_names',
            type: 'array',
            description: 'Other Names',
            items: new OA\Items(type: 'string'),
        ),
        new OA\Property(property: 'birthday', type: 'string', description: 'Birthday Date ISO8601', nullable: true),
        new OA\Property(property: 'favorites', type: 'integer', description: 'Number of users who have favorited this entry', nullable: true),
        new OA\Property(property: 'about', type: 'string', description: 'Biography', nullable: true),
    ],
)]
class PersonResource extends JsonResource
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
            'mal_id' => $this->mal_id,
            'url' => $this->url,
            'website_url' => $this->website_url,
            'images' => $this->images,
            'name' => $this->name,
            'given_name' => $this->given_name,
            'family_name' => $this->family_name,
            'alternate_names' => $this->alternate_names,
            'birthday' => $this->birthday,
            'favorites' => $this->favorites,
            'about' => $this->about,
        ];
    }
}
