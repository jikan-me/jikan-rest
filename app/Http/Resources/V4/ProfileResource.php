<?php

namespace App\Http\Resources\V4;

use App\Profile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin Profile */
#[OA\Schema(
    schema: 'user_profile',
    type: 'object',
    properties: [
        new OA\Property(property: 'mal_id', type: 'integer', description: 'MyAnimeList ID', nullable: true),
        new OA\Property(property: 'username', type: 'string', description: 'MyAnimeList Username'),
        new OA\Property(property: 'url', type: 'string', description: 'MyAnimeList URL'),
        new OA\Property(property: 'images', type: 'object', ref: '#/components/schemas/user_images'),
        new OA\Property(property: 'last_online', type: 'string', description: 'Last Online Date ISO8601', nullable: true),
        new OA\Property(property: 'gender', type: 'string', description: 'User Gender', nullable: true),
        new OA\Property(property: 'birthday', type: 'string', description: 'Birthday Date ISO8601', nullable: true),
        new OA\Property(property: 'location', type: 'string', description: 'Location', nullable: true),
        new OA\Property(property: 'joined', type: 'string', description: 'Joined Date ISO8601', nullable: true),
    ],
)]

#[OA\Schema(
    schema: 'users_temp',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'mal_id', type: 'integer', description: 'MyAnimeList ID'),
                    new OA\Property(property: 'username', type: 'string', description: 'MyAnimeList Username'),
                    new OA\Property(property: 'url', type: 'string', description: 'MyAnimeList URL'),
                    new OA\Property(
                        property: 'images',
                        type: 'object',
                        description: 'Images',
                        properties: [
                            new OA\Property(
                                property: 'jpg',
                                type: 'object',
                                description: 'Available images in JPG',
                                properties: [
                                    new OA\Property(property: 'image_url', type: 'string', description: 'Image URL JPG (225x335)'),
                                ],
                            ),
                            new OA\Property(
                                property: 'webp',
                                type: 'object',
                                description: 'Available images in WEBP',
                                properties: [
                                    new OA\Property(property: 'image_url', type: 'string', description: 'Image URL WEBP (225x335)'),
                                ],
                            ),
                        ],
                    ),
                    new OA\Property(property: 'last_online', type: 'string', description: 'Last Online Date ISO8601'),
                    new OA\Property(property: 'gender', type: 'string', description: 'User Gender'),
                    new OA\Property(property: 'birthday', type: 'string', description: 'Birthday Date ISO8601'),
                    new OA\Property(property: 'location', type: 'string', description: 'Location'),
                    new OA\Property(property: 'joined', type: 'string', description: 'Joined Date ISO8601'),
                    new OA\Property(
                        property: 'anime_stats',
                        type: 'object',
                        description: 'Anime Stats',
                        properties: [
                            new OA\Property(property: 'days_watched', type: 'number', format: 'float', description: 'Number of days spent watching Anime'),
                            new OA\Property(property: 'mean_score', type: 'number', format: 'float', description: 'Mean Score'),
                            new OA\Property(property: 'watching', type: 'integer', description: 'Anime Watching'),
                            new OA\Property(property: 'completed', type: 'integer', description: 'Anime Completed'),
                            new OA\Property(property: 'on_hold', type: 'integer', description: 'Anime On-Hold'),
                            new OA\Property(property: 'dropped', type: 'integer', description: 'Anime Dropped'),
                            new OA\Property(property: 'plan_to_watch', type: 'integer', description: 'Anime Planned to Watch'),
                            new OA\Property(property: 'total_entries', type: 'integer', description: 'Total Anime entries on User list'),
                            new OA\Property(property: 'rewatched', type: 'integer', description: 'Anime re-watched'),
                            new OA\Property(property: 'episodes_watched', type: 'integer', description: 'Number of Anime Episodes Watched'),
                        ],
                    ),
                    new OA\Property(
                        property: 'manga_stats',
                        type: 'object',
                        description: 'Manga Stats',
                        properties: [
                            new OA\Property(property: 'days_read', type: 'number', format: 'float', description: 'Number of days spent reading Manga'),
                            new OA\Property(property: 'mean_score', type: 'number', format: 'float', description: 'Mean Score'),
                            new OA\Property(property: 'reading', type: 'integer', description: 'Manga Reading'),
                            new OA\Property(property: 'completed', type: 'integer', description: 'Manga Completed'),
                            new OA\Property(property: 'on_hold', type: 'integer', description: 'Manga On-Hold'),
                            new OA\Property(property: 'dropped', type: 'integer', description: 'Manga Dropped'),
                            new OA\Property(property: 'plan_to_read', type: 'integer', description: 'Manga Planned to Read'),
                            new OA\Property(property: 'total_entries', type: 'integer', description: 'Total Manga entries on User list'),
                            new OA\Property(property: 'reread', type: 'integer', description: 'Manga re-read'),
                            new OA\Property(property: 'chapters_read', type: 'integer', description: 'Number of Manga Chapters Read'),
                            new OA\Property(property: 'volumes_read', type: 'integer', description: 'Number of Manga Volumes Read'),
                        ],
                    ),
                    new OA\Property(
                        property: 'favorites',
                        type: 'object',
                        description: 'Favorite entries',
                        properties: [
                            new OA\Property(
                                property: 'anime',
                                type: 'array',
                                description: 'Favorite Anime',
                                items: new OA\Items(type: 'object', ref: '#/components/schemas/entry_meta'),
                            ),
                            new OA\Property(
                                property: 'manga',
                                type: 'array',
                                description: 'Favorite Manga',
                                items: new OA\Items(type: 'object', ref: '#/components/schemas/entry_meta'),
                            ),
                            new OA\Property(
                                property: 'characters',
                                type: 'array',
                                description: 'Favorite Characters',
                                items: new OA\Items(type: 'object', ref: '#/components/schemas/entry_meta'),
                            ),
                            new OA\Property(
                                property: 'people',
                                type: 'array',
                                description: 'Favorite People',
                                items: new OA\Items(type: 'object', ref: '#/components/schemas/entry_meta'),
                            ),
                        ],
                    ),
                    new OA\Property(property: 'about', type: 'string', description: 'User About. NOTE: About information is customizable by users through BBCode on MyAnimeList. This means users can add multimedia content, different text sizes, etc. Due to this freeform, Jikan returns parsed HTML. Validate on your end! Null when the user has no About text.', nullable: true),
                ],
            ),
        ),
    ],
)]
class ProfileResource extends JsonResource
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
        return [
            'mal_id' => $this->mal_id,
            'username' => $this->username,
            'url' => $this->url,
            'images' => $this->images,
            'last_online' => $this->last_online,
            'gender' => $this->gender,
            'birthday' => $this->birthday,
            'location' => $this->location,
            'joined' => $this->joined,
        ];
    }
}
