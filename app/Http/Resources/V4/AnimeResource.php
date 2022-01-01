<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class AnimeResource extends JsonResource
{
    /**
     *  @OA\Schema(
     *      schema="anime",
     *      description="Anime Resource",
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
     *          ref="#/components/schemas/anime images"
     *      ),
     *      @OA\Property(
     *          property="trailer",
     *          ref="#/components/schemas/trailer base"
     *      ),
     *      @OA\Property(
     *          property="title",
     *          type="string",
     *          description="Title"
     *      ),
     *      @OA\Property(
     *          property="title_english",
     *          type="string",
     *          description="English Title"
     *      ),
     *      @OA\Property(
     *          property="title_japanese",
     *          type="string",
     *          description="Japanese Title"
     *      ),
     *      @OA\Property(
     *          property="title_synonyms",
     *          type="array",
     *          description="Other Titles",
     *          @OA\Items(
     *              type="string"
     *          )
     *      ),
     *      @OA\Property(
     *          property="type",
     *          type="string",
     *          enum={"TV","OVA","Movie","Special","ONA","Music"},
     *          description="Anime Type"
     *      ),
     *      @OA\Property(
     *          property="source",
     *          type="string",
     *          description="Original Material/Source adapted from"
     *      ),
     *      @OA\Property(
     *          property="episodes",
     *          type="integer",
     *          description="Episode count"
     *      ),
     *      @OA\Property(
     *          property="status",
     *          type="string",
     *          enum={"Finished Airing", "Currently Airing", "Not yet aired"},
     *          description="Airing status"
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
     *          description="Parsed raw duration"
     *      ),
     *      @OA\Property(
     *          property="rating",
     *          type="string",
     *          enum={"G - All Ages", "PG - Children", "PG-13 - Teens 13 or older", "R - 17+ (violence & profanity)", "R+ - Mild Nudity", "Rx - Hentai" },
     *          description="Anime audience rating"
     *      ),
     *      @OA\Property(
     *          property="score",
     *          type="number",
     *          format="float",
     *          description="Score"
     *      ),
     *      @OA\Property(
     *          property="scored_by",
     *          type="integer",
     *          description="Number of users"
     *      ),
     *      @OA\Property(
     *          property="rank",
     *          type="integer",
     *          description="Ranking"
     *      ),
     *      @OA\Property(
     *          property="popularity",
     *          type="integer",
     *          description="Popularity"
     *      ),
     *      @OA\Property(
     *          property="members",
     *          type="integer",
     *          description="Number of users who have added this entry to their list"
     *      ),
     *      @OA\Property(
     *          property="favorites",
     *          type="integer",
     *          description="Number of users who have favorited this entry"
     *      ),
     *      @OA\Property(
     *          property="synopsis",
     *          type="string",
     *          description="Synopsis"
     *      ),
     *      @OA\Property(
     *          property="background",
     *          type="string",
     *          description="Background"
     *      ),
     *      @OA\Property(
     *          property="season",
     *          type="string",
     *          enum={"Summer", "Winter", "Spring", "Fall"},
     *          description="Season"
     *      ),
     *      @OA\Property(
     *          property="year",
     *          type="integer",
     *          description="Year"
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