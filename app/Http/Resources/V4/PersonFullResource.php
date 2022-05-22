<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class PersonFullResource extends JsonResource
{

    /**
     *  @OA\Schema(
     *      schema="person_full",
     *      description="Person Resource",
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
     *          property="website_url",
     *          type="string",
     *          description="Person's website URL",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="images",
     *          type="object",
     *          ref="#/components/schemas/people_images",
     *      ),
     *      @OA\Property(
     *          property="name",
     *          type="string",
     *          description="Name"
     *      ),
     *      @OA\Property(
     *          property="given_name",
     *          type="string",
     *          description="Given Name",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="family_name",
     *          type="string",
     *          description="Family Name",
     *          nullable=true
     *      ),
     *      @OA\Property(
     *          property="alternate_names",
     *          type="array",
     *          description="Other Names",
     *          @OA\Items(
     *              type="string"
     *          )
     *      ),
     *      @OA\Property(
     *          property="birthday",
     *          type="string",
     *          description="Birthday Date ISO8601",
     *          nullable=true
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
     *                  property="position",
     *                  type="string",
     *                  description="Person's position"
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
     *                  property="position",
     *                  type="string",
     *                  description="Person's position"
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
     *                  property="role",
     *                  type="string",
     *                  description="Person's Character's role in the anime"
     *              ),
     *              @OA\Property(
     *                  property="anime",
     *                  type="object",
     *                  description="The anime in which the person is voice acting in",
     *                  ref="#/components/schemas/anime_meta"
     *              ),
     *              @OA\Property(
     *                  property="character",
     *                  type="object",
     *                  description="The character the person is voice acting for",
     *                  ref="#/components/schemas/character_meta"
     *              ),
     *          ),
     *     ),
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
            'website_url' => $this->website_url,
            'images' => $this->images,
            'name' => $this->name,
            'given_name' => $this->given_name,
            'family_name' => $this->family_name,
            'alternate_names' => $this->alternate_names,
            'birthday' => $this->birthday,
            'favorites' => $this->favorites,
            'about' => $this->about,
            'anime' => $this->anime_staff_positions,
            'manga' => $this->published_manga,
            'voices' => $this->voice_acting_roles,
        ];
    }
}