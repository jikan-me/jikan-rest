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
     *          ref="#/components/schemas/people images",
     *      ),
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
     *          description="Biography"
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