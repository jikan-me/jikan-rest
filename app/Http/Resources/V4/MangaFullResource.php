<?php

namespace App\Http\Resources\V4;

use App\Manga;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin Manga */
#[OA\Schema(
    schema: 'manga_full',
    description: 'Manga Resource',
    properties: [
        new OA\Property(property: 'mal_id', type: 'integer', description: 'MyAnimeList ID'),
        new OA\Property(property: 'url', type: 'string', description: 'MyAnimeList URL'),
        new OA\Property(property: 'images', ref: '#/components/schemas/manga_images'),
        new OA\Property(property: 'approved', type: 'boolean', description: 'Whether the entry is pending approval on MAL or not'),
        new OA\Property(
            property: 'titles',
            type: 'array',
            description: 'All Titles',
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
        new OA\Property(property: 'type', type: 'string', enum: ['Manga', 'Novel', 'Light Novel', 'One-shot', 'Doujinshi', 'Manhua', 'Manhwa', 'OEL'], description: 'Manga Type', nullable: true),
        new OA\Property(property: 'chapters', type: 'integer', description: 'Chapter count', nullable: true),
        new OA\Property(property: 'volumes', type: 'integer', description: 'Volume count', nullable: true),
        new OA\Property(property: 'status', type: 'string', enum: ['Finished', 'Publishing', 'On Hiatus', 'Discontinued', 'Not yet published'], description: 'Publishing status'),
        new OA\Property(property: 'publishing', type: 'boolean', description: 'Publishing boolean'),
        new OA\Property(property: 'published', ref: '#/components/schemas/daterange'),
        new OA\Property(property: 'score', type: 'number', format: 'float', description: 'Score', nullable: true),
        new OA\Property(property: 'scored_by', type: 'integer', description: 'Number of users', nullable: true),
        new OA\Property(property: 'rank', type: 'integer', description: 'Ranking', nullable: true),
        new OA\Property(property: 'popularity', type: 'integer', description: 'Popularity', nullable: true),
        new OA\Property(property: 'members', type: 'integer', description: 'Number of users who have added this entry to their list', nullable: true),
        new OA\Property(property: 'favorites', type: 'integer', description: 'Number of users who have favorited this entry', nullable: true),
        new OA\Property(property: 'synopsis', type: 'string', description: 'Synopsis', nullable: true),
        new OA\Property(property: 'background', type: 'string', description: 'Background', nullable: true),
        new OA\Property(
            property: 'authors',
            type: 'array',
            items: new OA\Items(type: 'object', ref: '#/components/schemas/mal_url'),
        ),
        new OA\Property(
            property: 'serializations',
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
        new OA\Property(
            property: 'relations',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'relation', type: 'string', description: 'Relation type'),
                    new OA\Property(
                        property: 'entry',
                        type: 'array',
                        items: new OA\Items(type: 'object', ref: '#/components/schemas/mal_url'),
                    ),
                ],
            ),
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
class MangaFullResource extends JsonResource
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
            'approved' => $this->approved ?? true,
            'titles' => $this->titles ?? [],
            'title' => $this->title,
            'title_english' => $this->title_english,
            'title_japanese' => $this->title_japanese,
            'title_synonyms' => $this->title_synonyms,
            'type' => $this->type,
            'chapters' => $this->chapters,
            'volumes' => $this->volumes,
            'status' => $this->status,
            'publishing' => $this->publishing,
            'published' => $this->published,
            'score' => $this->score,
            'scored' => $this->score, // @todo remove in 4.1
            'scored_by' => $this->scored_by,
            'rank' => $this->rank,
            'popularity' => $this->popularity,
            'members' => $this->members,
            'favorites' => $this->favorites,
            'synopsis' => $this->synopsis,
            'background' => $this->background,
            'authors' => $this->authors,
            'serializations' => $this->serializations,
            'genres' => $this->genres,
            'explicit_genres' => $this->explicit_genres,
            'themes' => $this->themes,
            'demographics' => $this->demographics,
            'relations' => $this->related,
            'external' => $this->external_links,
        ];
    }
}
