<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class ClubResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     * @OA\Schema(
     *     schema="club",
     *     description="Club Resource",
     *
     *      @OA\Property(
     *          property="data",
     *          type="object",
     *
     *          @OA\Property(
     *              property="mal_id",
     *              type="integer",
     *              description="MyAnimeList ID"
     *          ),
     *          @OA\Property(
     *              property="url",
     *              type="string",
     *              description="MyAnimeList URL"
     *          ),
     *          @OA\Property(
     *              property="images",
     *              type="object",
     *              description="Images",
     *              @OA\Property(
     *                  property="jpg",
     *                  type="object",
     *                  description="Available images in JPG",
     *                  @OA\Property(
     *                      property="image_url",
     *                      type="string",
     *                      description="Image URL JPG (225x335)",
     *                  ),
     *              ),
     *          ),
     *          @OA\Property(
     *              property="members_count",
     *              type="integer",
     *              description="Number of club members"
     *          ),
     *          @OA\Property(
     *              property="pictures_count",
     *              type="integer",
     *              description="Number of club pictures"
     *          ),
     *          @OA\Property(
     *              property="category",
     *              type="string",
     *              description="Club Category",
     *              enum={"Actors & Artists", "Anime", "Characters", "Cities & Neighborhoods", "Companies", "Conventions", "Games", "Japan", "Manga", "Music", "Others", "Schools"}
     *          ),
     *          @OA\Property(
     *              property="created",
     *              type="string",
     *              description="Date Created ISO8601"
     *          ),
     *          @OA\Property(
     *              property="type",
     *              type="string",
     *              description="Type",
     *              enum={"public", "private", "secret"}
     *          ),
     *          @OA\Property(
     *              property="staff",
     *              type="array",
     *              description="Staff members",
     *              @OA\Items(
     *                  type="object",
     *                  description="Staff member",
     *                  @OA\Property(
     *                      property="url",
     *                      type="string",
     *                      description="MyAnimeList URL"
     *                  ),
     *                  @OA\Property(
     *                      property="username",
     *                      type="string",
     *                      description="MyAnimeList Username"
     *                  ),
     *              ),
     *          ),
     *          @OA\Property(
     *              property="anime_relations",
     *              type="array",
     *              description="Anime Relations",
     *              @OA\Items(
     *                  type="object",
     *                  description="Resource",
     *                  ref="#/components/schemas/mal_url"
     *              ),
     *          ),
     *          @OA\Property(
     *              property="manga_relations",
     *              type="array",
     *              description="Manga Relations",
     *              @OA\Items(
     *                  type="object",
     *                  description="Resource",
     *                  ref="#/components/schemas/mal_url"
     *              ),
     *          ),
     *          @OA\Property(
     *              property="character_relations",
     *              type="array",
     *              description="Character Relations",
     *              @OA\Items(
     *                  type="object",
     *                  description="Resource",
     *                  ref="#/components/schemas/mal_url"
     *              ),
     *          ),
     *     ),
     * ),
     */
    public function toArray($request)
    {
        return [
            'mal_id' => $this->mal_id,
            'url' => $this->url,
            'images' => $this->image,
            'title' => $this->title,
            'members_count' => $this->members_count,
            'pictures_count' => $this->pictures_count,
            'category' => $this->category,
            'created' => $this->created,
            'type' => $this->type,
            'anime_relations' => $this->anime_relations,
            'manga_relations' => $this->manga_relations,
            'character_relations' => $this->character_relations,
        ];
    }
}