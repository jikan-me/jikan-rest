<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class CharacterFullResource extends JsonResource
{
    /**
     *  @OA\Schema(
     *      schema="character_full",
     *      description="Character Resource",
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
     *          ref="#/components/schemas/character_images"
     *      ),
     *      @OA\Property(
     *          property="name",
     *          type="string",
     *          description="Name"
     *      ),
     *      @OA\Property(
     *          property="name_kanji",
     *          type="string",
     *          description="Name",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="nicknames",
     *          type="array",
     *          description="Other Names",
     *          @OA\Items(
     *              type="string"
     *          )
     *      ),
     *      @OA\Property(
     *          property="favorites",
     *          type="integer",
     *          description="Number of users who have favorited this entry"
     *      ),
     *      @OA\Property(
     *          property="about",
     *          type="string",
     *          description="Biography",
     *          nullable=true
     *      ),
     *
     *     @OA\Property(
     *          property="anime",
     *          type="array",
     *
     *          @OA\Items(
     *              type="object",
     *
     *              @OA\Property(
     *                  property="role",
     *                  type="string",
     *                  description="Character's Role"
     *              ),
     *              @OA\Property(
     *                  property="anime",
     *                  type="object",
     *                  ref="#/components/schemas/anime_meta"
     *              ),
     *          ),
     *     ),
     *
     *     @OA\Property(
     *          property="manga",
     *          type="array",
     *
     *          @OA\Items(
     *              type="object",
     *
     *              @OA\Property(
     *                  property="role",
     *                  type="string",
     *                  description="Character's Role"
     *              ),
     *              @OA\Property(
     *                  property="manga",
     *                  type="object",
     *                  ref="#/components/schemas/manga_meta"
     *              ),
     *          ),
     *     ),
     *
     *     @OA\Property(
     *          property="voices",
     *          type="array",
     *
     *          @OA\Items(
     *              type="object",
     *
     *              @OA\Property(
     *                  property="language",
     *                  type="string",
     *                  description="Character's Role"
     *              ),
     *              @OA\Property(
     *                  property="person",
     *                  type="object",
     *                  ref="#/components/schemas/person_meta"
     *              ),
     *          ),
     *     ),
     *
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
            'name' => $this->name,
            'name_kanji' => $this->name_kanji,
            'nicknames' => $this->nicknames,
            'favorites' => $this->favorites,
            'about' => $this->about,
            'anime' => $this->animeography,
            'manga' => $this->mangaography,
            'voices' => $this->voice_actors,
        ];
    }
}