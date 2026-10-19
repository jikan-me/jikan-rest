<?php

namespace App\Http\Resources\V4;

use App\Profile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin Profile */
#[OA\Schema(
    schema: 'user_statistics',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'data',
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
    ],
)]
class ProfileStatisticsResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     *
     * ),
     */
    /**
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'anime' => $this->anime_stats,
            'manga' => $this->manga_stats,
        ];
    }
}
