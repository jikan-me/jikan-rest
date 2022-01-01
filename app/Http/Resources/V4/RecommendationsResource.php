<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class RecommendationsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     *  @OA\Schema(
     *      schema="recommendations",
     *      description="Recommendations",
     *
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/pagination"),
     *          @OA\Schema(
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *
     *                  @OA\Items(
     *                      type="object",
     *
     *                      @OA\Property(
     *                          property="mal_id",
     *                          type="String",
     *                          description="MAL IDs of recommendations is both of the MAL ID's with a `-` delimiter",
     *                      ),
     *
     *                      @OA\Property (
     *                          property="entry",
     *                          type="array",
     *                          description="Array of 2 entries that are being recommended to each other",
     *
     *                          @OA\Items(
     *                              type="object",
     *                              anyOf={
     *                                  @OA\Schema(ref="#/components/schemas/anime meta"),
     *                                  @OA\Schema(ref="#/components/schemas/manga meta"),
     *                              }
     *                          ),
     *                      ),
     *
     *                      @OA\Property (
     *                          property="content",
     *                          type="string",
     *                          description="Recommendation context provided by the user",
     *                      ),
     *
     *                      @OA\Property (
     *                          property="user",
     *                          type="object",
     *                          ref="#/components/schemas/user by id",
     *                      ),
     *                  ),
     *              ),
     *          ),
     *     }
     *  ),
     *
     *
     * @OA\Schema(
     *      schema="entry recommendations",
     *      description="Entry Recommendations Resource",
     *
     *     @OA\Property(
     *          property="data",
     *          type="array",
     *
     *          @OA\Items(
     *              type="object",

     *              @OA\Property (
     *                  property="entry",
     *                  type="array",
     *                  description="Array of 2 entries that are being recommended to each other",
     *
     *                  @OA\Items(
     *                      type="object",
     *                      anyOf={
     *                          @OA\Schema(ref="#/components/schemas/anime meta"),
     *                          @OA\Schema(ref="#/components/schemas/manga meta"),
     *                      }
     *                  ),
     *              ),
     *              @OA\Property(
     *                  property="url",
     *                  type="string",
     *                  description="Recommendation MyAnimeList URL"
     *              ),
     *              @OA\Property(
     *                  property="votes",
     *                  type="integer",
     *                  description="Number of users who have recommended this entry"
     *              ),
     *          ),
     *     ),
     *  )
     */
    public function toArray($request)
    {
        return $this['recommendations'];
    }
}