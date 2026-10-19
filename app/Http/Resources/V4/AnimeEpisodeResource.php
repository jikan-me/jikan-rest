<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'anime_episode',
    description: 'Anime Episode Resource',
    properties: [
        new OA\Property(property: 'mal_id', type: 'integer', description: 'MyAnimeList ID'),
        new OA\Property(property: 'url', type: 'string', description: 'MyAnimeList URL'),
        new OA\Property(property: 'title', type: 'string', description: 'Title'),
        new OA\Property(property: 'title_japanese', type: 'string', description: 'Title Japanese', nullable: true),
        new OA\Property(property: 'title_romanji', type: 'string', description: 'title_romanji', nullable: true),
        new OA\Property(property: 'duration', type: 'integer', description: 'Episode duration in seconds', nullable: true),
        new OA\Property(property: 'aired', type: 'string', description: 'Aired Date ISO8601', nullable: true),
        new OA\Property(property: 'filler', type: 'boolean', description: 'Filler episode'),
        new OA\Property(property: 'recap', type: 'boolean', description: 'Recap episode'),
        new OA\Property(property: 'synopsis', type: 'string', description: 'Episode Synopsis', nullable: true),
    ],
)]
class AnimeEpisodeResource extends JsonResource
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
            'mal_id' => $this['mal_id'],
            'url' => $this['url'],
            'title' => $this['title'],
            'title_japanese' => $this['title_japanese'],
            'title_romanji' => $this['title_romanji'],
            'duration' => $this['duration'],
            'aired' => $this['aired'],
            'filler' => $this['filler'],
            'recap' => $this['recap'],
            'synopsis' => $this['synopsis'],
        ];
    }
}
