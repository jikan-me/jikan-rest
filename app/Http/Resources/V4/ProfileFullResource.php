<?php

namespace App\Http\Resources\V4;

use App\Profile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin Profile */
#[OA\Schema(
    schema: 'user_profile_full',
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
        new OA\Property(property: 'about', type: 'string', description: 'User About (parsed HTML). Null when the user has no About text.', nullable: true),
        new OA\Property(
            property: 'statistics',
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'anime',
                    type: 'object',
                    description: 'Anime Statistics',
                    properties: [
                        new OA\Property(property: 'days_watched', type: 'number', format: 'float', description: 'Number of days spent watching Anime', nullable: true),
                        new OA\Property(property: 'mean_score', type: 'number', format: 'float', description: 'Mean Score', nullable: true),
                        new OA\Property(property: 'watching', type: 'integer', description: 'Anime Watching'),
                        new OA\Property(property: 'completed', type: 'integer', description: 'Anime Completed'),
                        new OA\Property(property: 'on_hold', type: 'integer', description: 'Anime On-Hold'),
                        new OA\Property(property: 'dropped', type: 'integer', description: 'Anime Dropped'),
                        new OA\Property(property: 'plan_to_watch', type: 'integer', description: 'Anime Planned to Watch'),
                        new OA\Property(property: 'total_entries', type: 'integer', description: 'Total Anime entries on User list', nullable: true),
                        new OA\Property(property: 'rewatched', type: 'integer', description: 'Anime re-watched', nullable: true),
                        new OA\Property(property: 'episodes_watched', type: 'integer', description: 'Number of Anime Episodes Watched', nullable: true),
                    ],
                ),
                new OA\Property(
                    property: 'manga',
                    type: 'object',
                    description: 'Manga Statistics',
                    properties: [
                        new OA\Property(property: 'days_read', type: 'number', format: 'float', description: 'Number of days spent reading Manga', nullable: true),
                        new OA\Property(property: 'mean_score', type: 'number', format: 'float', description: 'Mean Score', nullable: true),
                        new OA\Property(property: 'reading', type: 'integer', description: 'Manga Reading'),
                        new OA\Property(property: 'completed', type: 'integer', description: 'Manga Completed'),
                        new OA\Property(property: 'on_hold', type: 'integer', description: 'Manga On-Hold'),
                        new OA\Property(property: 'dropped', type: 'integer', description: 'Manga Dropped'),
                        new OA\Property(property: 'plan_to_read', type: 'integer', description: 'Manga Planned to Read'),
                        new OA\Property(property: 'total_entries', type: 'integer', description: 'Total Manga entries on User list', nullable: true),
                        new OA\Property(property: 'reread', type: 'integer', description: 'Manga re-read', nullable: true),
                        new OA\Property(property: 'chapters_read', type: 'integer', description: 'Number of Manga Chapters Read', nullable: true),
                        new OA\Property(property: 'volumes_read', type: 'integer', description: 'Number of Manga Volumes Read', nullable: true),
                    ],
                ),
            ],
        ),
        new OA\Property(
            property: 'external',
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
class ProfileFullResource extends JsonResource
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
            'statistics' => [
                'anime' => $this->anime_stats,
                'manga' => $this->manga_stats,
            ],
            'favorites' => $this->favorites,
            'updates' => $this->last_updates,
            'about' => $this->about,
            'external' => $this->external_links,
        ];
    }
}
