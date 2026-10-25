<?php

namespace App\Http\Resources\V4;

use App\Person;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin Person */
#[OA\Schema(
    schema: 'person_full',
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
        new OA\Property(
            property: 'anime',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'position', type: 'string', description: 'Person\'s position'),
                    new OA\Property(property: 'anime', type: 'object', ref: '#/components/schemas/anime_meta'),
                ],
            ),
        ),
        new OA\Property(
            property: 'manga',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'position', type: 'string', description: 'Person\'s position'),
                    new OA\Property(property: 'manga', type: 'object', ref: '#/components/schemas/manga_meta'),
                ],
            ),
        ),
        new OA\Property(
            property: 'voices',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'role', type: 'string', description: 'Person\'s Character\'s role in the anime', nullable: true),
                    new OA\Property(property: 'anime', type: 'object', description: 'The anime in which the person is voice acting in', ref: '#/components/schemas/anime_meta'),
                    new OA\Property(property: 'character', type: 'object', description: 'The character the person is voice acting for', ref: '#/components/schemas/character_meta'),
                ],
            ),
        ),
    ],
)]
class PersonFullResource extends JsonResource
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
            'anime' => $this->anime_staff_positions,
            'manga' => $this->published_manga,
            'voices' => $this->voice_acting_roles,
        ];
    }
}
