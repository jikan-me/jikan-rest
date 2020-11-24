<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
{

    /**
     *  @OA\Schema(
     *      schema="person",
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
     *          description="Person's website URL"
     *      ),
     *      @OA\Property(
     *          property="images",
     *          type="object",
     *          description="Images",
     *          @OA\Property(
     *              property="jpg",
     *              type="object",
     *              description="Available images in JPG",
     *              @OA\Property(
     *                  property="image_url",
     *                  type="string",
     *                  description="Image URL JPG (225x350)",
     *              ),
     *          ),
     *          @OA\Property(
     *              property="webp",
     *              type="object",
     *              description="Available images in WEBP",
     *              @OA\Property(
     *                  property="image_url",
     *                  type="string",
     *                  description="Image URL WEBP (225x350)",
     *              ),
     *          ),
     *      ),
     *
     *      @OA\Property(
     *          property="name",
     *          type="string",
     *          description="Name"
     *      ),
     *      @OA\Property(
     *          property="given_name",
     *          type="string",
     *          description="Given Name"
     *      ),
     *      @OA\Property(
     *          property="family_name",
     *          type="string",
     *          description="Family Name"
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
     *          description="Birthday Date ISO8601"
     *      ),
     *      @OA\Property(
     *          property="favorites",
     *          type="integer",
     *          description="Number of users who have favorited this entry"
     *      ),
     *      @OA\Property(
     *          property="about",
     *          type="string",
     *          description="Synopsis"
     *      ),
     *      @OA\Property(
     *          property="voice_acting_roles",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              @OA\Property(
     *                  property="role",
     *                  type="string",
     *                  description="Role"
     *              ),
     *              @OA\Property(
     *                  property="anime",
     *                  ref="#/components/schemas/mal_url"
     *              ),
     *              @OA\Property(
     *                  property="character",
     *                  ref="#/components/schemas/mal_url"
     *              ),
     *          ),
     *      ),
     *      @OA\Property(
     *          property="anime_staff_positions",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              @OA\Property(
     *                  property="position",
     *                  type="string",
     *                  description="Position"
     *              ),
     *              @OA\Property(
     *                  property="anime",
     *                  ref="#/components/schemas/mal_url"
     *              ),
     *          ),
     *      ),
     *      @OA\Property(
     *          property="published_manga",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *              @OA\Property(
     *                  property="position",
     *                  type="string",
     *                  description="Position"
     *              ),
     *              @OA\Property(
     *                  property="manga",
     *                  ref="#/components/schemas/mal_url"
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
            'website_url' => $this->website_url,
            'images' => $this->images,
            'name' => $this->name,
            'given_name' => $this->given_name,
            'family_name' => $this->family_name,
            'alternate_names' => $this->alternate_names,
            'birthday' => $this->birthday,
            'favorites' => $this->favorites,
            'about' => $this->about
        ];
    }
}