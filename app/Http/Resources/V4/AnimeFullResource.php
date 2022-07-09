<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class AnimeFullResource extends JsonResource
{
    /**
     *  @OA\Schema(
     *      schema="anime_full",
     *      description="Full anime Resource",
     *
     *      @OA\Property(
     *          property="mal_id",
     *          type="integer",
     *          description="MyAnimeList ID"
     *      ),
     *      @OA\Property(
     *          property="url",
     *          type="string",
     *          description="MyAnimeList URL"
     *      ),
     *      @OA\Property(
     *          property="images",
     *          ref="#/components/schemas/anime_images"
     *      ),
     *      @OA\Property(
     *          property="trailer",
     *          ref="#/components/schemas/trailer_base"
     *      ),
     *      @OA\Property(
     *          property="approved",
     *          type="boolean",
     *          description="Whether the entry is pending approval on MAL or not"
     *      ),
     *      @OA\Property(
     *          property="titles",
     *          type="array",
     *          description="All titles",
     *          @OA\Items(
     *              type="string"
     *          )
     *      ),
     *      @OA\Property(
     *          property="title",
     *          type="string",
     *          description="Title",
     *          deprecated=true
     *      ),
     *      @OA\Property(
     *          property="title_english",
     *          type="string",
     *          description="English Title",
     *          nullable=true,
     *          deprecated=true
     *      ),
     *      @OA\Property(
     *          property="title_japanese",
     *          type="string",
     *          description="Japanese Title",
     *          nullable=true,
     *          deprecated=true
     *      ),
     *      @OA\Property(
     *          property="title_synonyms",
     *          type="array",
     *          description="Other Titles",
     *          @OA\Items(
     *              type="string"
     *          ),
     *          deprecated=true
     *      ),
     *      @OA\Property(
     *          property="type",
     *          type="string",
     *          enum={"TV","OVA","Movie","Special","ONA","Music"},
     *          description="Anime Type",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="source",
     *          type="string",
     *          description="Original Material/Source adapted from",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="episodes",
     *          type="integer",
     *          description="Episode count",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="status",
     *          type="string",
     *          enum={"Finished Airing", "Currently Airing", "Not yet aired"},
     *          description="Airing status",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="airing",
     *          type="boolean",
     *          description="Airing boolean"
     *      ),
     *      @OA\Property(
     *          property="aired",
     *          ref="#/components/schemas/daterange"
     *      ),
     *      @OA\Property(
     *          property="duration",
     *          type="string",
     *          description="Parsed raw duration",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="rating",
     *          type="string",
     *          enum={"G - All Ages", "PG - Children", "PG-13 - Teens 13 or older", "R - 17+ (violence & profanity)", "R+ - Mild Nudity", "Rx - Hentai" },
     *          description="Anime audience rating",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="score",
     *          type="number",
     *          format="float",
     *          description="Score",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="scored_by",
     *          type="integer",
     *          description="Number of users",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="rank",
     *          type="integer",
     *          description="Ranking",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="popularity",
     *          type="integer",
     *          description="Popularity",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="members",
     *          type="integer",
     *          description="Number of users who have added this entry to their list",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="favorites",
     *          type="integer",
     *          description="Number of users who have favorited this entry",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="synopsis",
     *          type="string",
     *          description="Synopsis",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="background",
     *          type="string",
     *          description="Background",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="season",
     *          type="string",
     *          enum={"summer", "winter", "spring", "fall"},
     *          description="Season",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="year",
     *          type="integer",
     *          description="Year",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="broadcast",
     *          ref="#/components/schemas/broadcast"
     *      ),
     *      @OA\Property(
     *          property="producers",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              ref="#/components/schemas/mal_url"
     *          ),
     *      ),
     *      @OA\Property(
     *          property="licensors",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              ref="#/components/schemas/mal_url"
     *          ),
     *      ),
     *      @OA\Property(
     *          property="studios",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              ref="#/components/schemas/mal_url"
     *          ),
     *      ),
     *      @OA\Property(
     *          property="genres",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              ref="#/components/schemas/mal_url"
     *          ),
     *      ),
     *      @OA\Property(
     *          property="explicit_genres",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              ref="#/components/schemas/mal_url"
     *          ),
     *      ),
     *      @OA\Property(
     *          property="themes",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              ref="#/components/schemas/mal_url"
     *          ),
     *      ),
     *      @OA\Property(
     *          property="demographics",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              ref="#/components/schemas/mal_url"
     *          ),
     *      ),
     *
     *     @OA\Property(
     *          property="relations",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *
     *              @OA\Property(
     *                  property="relation",
     *                  type="string",
     *                  description="Relation type"
     *              ),
     *              @OA\Property(
     *                  property="entry",
     *                  type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      ref="#/components/schemas/mal_url"
     *                  ),
     *              )
     *          )
     *     ),
     *
     *      @OA\Property(
     *          property="theme",
     *          type="object",
     *          @OA\Property(
     *               property="openings",
     *               type="array",
     *               @OA\Items(
     *                   type="string",
     *               ),
     *          ),
     *          @OA\Property(
     *               property="endings",
     *               type="array",
     *               @OA\Items(
     *                   type="string",
     *               ),
     *          ),
     *      ),
     *
     *      @OA\Property(
     *          property="external",
     *          type="array",
     *
     *          @OA\Items(
     *              type="object",
     *
     *              @OA\Property(
     *                   property="name",
     *                   type="string",
     *              ),
     *              @OA\Property(
     *                   property="url",
     *                   type="string",
     *              ),
     *          ),
     *      ),
     *
     *      @OA\Property(
     *          property="streaming",
     *          type="array",
     *
     *          @OA\Items(
     *              type="object",
     *
     *              @OA\Property(
     *                   property="name",
     *                   type="string",
     *              ),
     *              @OA\Property(
     *                   property="url",
     *                   type="string",
     *              ),
     *          ),
     *      ),
     *  )
     */

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
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
            'relations' => $this->related,
            'theme' => [
                'openings' => $this->opening_themes,
                'endings' => $this->ending_themes
            ],
            'external' => $this->external_links,
            'streaming' => $this->streaming_links,
        ];
    }
}
