<?php

namespace App\Http\Resources\V4;

use App\Anime;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @mixin Anime
 */
#[OA\Schema(
    schema: 'anime',
    description: 'Anime Resource',
    properties: [
        new OA\Property(property: 'mal_id', type: 'integer', description: 'MyAnimeList ID'),
        new OA\Property(property: 'url', type: 'string', description: 'MyAnimeList URL'),
        new OA\Property(property: 'images', ref: '#/components/schemas/anime_images'),
        new OA\Property(property: 'trailer', ref: '#/components/schemas/trailer_base'),
        new OA\Property(property: 'approved', type: 'boolean', description: 'Whether the entry is pending approval on MAL or not'),
        new OA\Property(
            property: 'titles',
            type: 'array',
            description: 'All titles',
            items: new OA\Items(type: 'object', ref: '#/components/schemas/title'),
        ),
        new OA\Property(property: 'title', type: 'string', description: 'Title', deprecated: true),
        new OA\Property(property: 'title_english', type: 'string', description: 'English Title', nullable: true, deprecated: true),
        new OA\Property(property: 'title_japanese', type: 'string', description: 'Japanese Title', nullable: true, deprecated: true),
        new OA\Property(
            property: 'title_synonyms',
            type: 'array',
            description: 'Other Titles',
            deprecated: true,
            items: new OA\Items(type: 'string'),
        ),
        new OA\Property(property: 'type', type: 'string', enum: ['TV', 'OVA', 'Movie', 'Special', 'ONA', 'Music'], description: 'Anime Type', nullable: true),
        new OA\Property(property: 'source', type: 'string', description: 'Original Material/Source adapted from', nullable: true),
        new OA\Property(property: 'episodes', type: 'integer', description: 'Episode count', nullable: true),
        new OA\Property(property: 'status', type: 'string', enum: ['Finished Airing', 'Currently Airing', 'Not yet aired'], description: 'Airing status', nullable: true),
        new OA\Property(property: 'airing', type: 'boolean', description: 'Airing boolean'),
        new OA\Property(property: 'aired', ref: '#/components/schemas/daterange'),
        new OA\Property(property: 'duration', type: 'string', description: 'Parsed raw duration', nullable: true),
        new OA\Property(property: 'rating', type: 'string', enum: ['G - All Ages', 'PG - Children', 'PG-13 - Teens 13 or older', 'R - 17+ (violence & profanity)', 'R+ - Mild Nudity', 'Rx - Hentai'], description: 'Anime audience rating', nullable: true),
        new OA\Property(property: 'score', type: 'number', format: 'float', description: 'Score', nullable: true),
        new OA\Property(property: 'scored_by', type: 'integer', description: 'Number of users', nullable: true),
        new OA\Property(property: 'rank', type: 'integer', description: 'Ranking', nullable: true),
        new OA\Property(property: 'popularity', type: 'integer', description: 'Popularity', nullable: true),
        new OA\Property(property: 'members', type: 'integer', description: 'Number of users who have added this entry to their list', nullable: true),
        new OA\Property(property: 'favorites', type: 'integer', description: 'Number of users who have favorited this entry', nullable: true),
        new OA\Property(property: 'synopsis', type: 'string', description: 'Synopsis', nullable: true),
        new OA\Property(property: 'background', type: 'string', description: 'Background', nullable: true),
        new OA\Property(property: 'season', type: 'string', enum: ['summer', 'winter', 'spring', 'fall'], description: 'Season', nullable: true),
        new OA\Property(property: 'year', type: 'integer', description: 'Year', nullable: true),
        new OA\Property(property: 'broadcast', ref: '#/components/schemas/broadcast'),
        new OA\Property(
            property: 'producers',
            type: 'array',
            items: new OA\Items(type: 'object', ref: '#/components/schemas/mal_url'),
        ),
        new OA\Property(
            property: 'licensors',
            type: 'array',
            items: new OA\Items(type: 'object', ref: '#/components/schemas/mal_url'),
        ),
        new OA\Property(
            property: 'studios',
            type: 'array',
            items: new OA\Items(type: 'object', ref: '#/components/schemas/mal_url'),
        ),
        new OA\Property(
            property: 'genres',
            type: 'array',
            items: new OA\Items(type: 'object', ref: '#/components/schemas/mal_url'),
        ),
        new OA\Property(
            property: 'explicit_genres',
            type: 'array',
            items: new OA\Items(type: 'object', ref: '#/components/schemas/mal_url'),
        ),
        new OA\Property(
            property: 'themes',
            type: 'array',
            items: new OA\Items(type: 'object', ref: '#/components/schemas/mal_url'),
        ),
        new OA\Property(
            property: 'demographics',
            type: 'array',
            items: new OA\Items(type: 'object', ref: '#/components/schemas/mal_url'),
        ),
    ],
)]
class AnimeResource extends JsonResource
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
            'images' => $this->images,
            'trailer' => $this->trailer,
            'approved' => $this->approved ?? true,
            'titles' => $this->titles ?? [],
            'title' => $this->title,
            'title_english' => $this->title_english,
            'title_japanese' => $this->title_japanese,
            'title_synonyms' => $this->title_synonyms,
            'type' => $this->type,
            'source' => $this->source,
            'episodes' => $this->episodes,
            'status' => $this->status,
            'airing' => $this->airing,
            'aired' => $this->aired,
            'duration' => $this->duration,
            'rating' => $this->rating,
            'score' => $this->score,
            'scored_by' => $this->scored_by,
            'rank' => $this->rank,
            'popularity' => $this->popularity,
            'members' => $this->members,
            'favorites' => $this->favorites,
            'synopsis' => $this->synopsis,
            'background' => $this->background,
            'season' => $this->season,
            'year' => $this->year,
            'broadcast' => $this->broadcast,
            'producers' => $this->producers,
            'licensors' => $this->licensors,
            'studios' => $this->studios,
            'genres' => $this->genres,
            'explicit_genres' => $this->explicit_genres,
            'themes' => $this->themes,
            'demographics' => $this->demographics,
        ];
    }
}
