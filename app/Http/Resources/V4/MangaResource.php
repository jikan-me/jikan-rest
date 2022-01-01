<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class MangaResource extends JsonResource
{
    /**
     *  @OA\Schema(
     *      schema="manga",
     *      description="Manga Resource",
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
     *          ref="#/components/schemas/manga images"
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
     *          enum={"Manga", "Novel", "One-shot", "Doujinshi", "Manhua", "Manhwa", "OEL"},
     *          description="Manga Type"
     *      ),
     *      @OA\Property(
     *          property="chapters",
     *          type="integer",
     *          description="Chapter count"
     *      ),
     *      @OA\Property(
     *          property="volumnes",
     *          type="integer",
     *          description="Volume count"
     *      ),
     *      @OA\Property(
     *          property="status",
     *          type="string",
     *          enum={"Finished", "Publishing", "On Hiatus", "Discontinued", "Not yet published"},
     *          description="Publishing status"
     *      ),
     *      @OA\Property(
     *          property="publishing",
     *          type="boolean",
     *          description="Publishing boolean"
     *      ),
     *      @OA\Property(
     *          property="published",
     *          ref="#/components/schemas/daterange"
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
     *          property="authors",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              ref="#/components/schemas/mal_url"
     *          ),
     *      ),
     *      @OA\Property(
     *          property="serializations",
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
            'scored' => $this->score,
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
        ];
    }
}