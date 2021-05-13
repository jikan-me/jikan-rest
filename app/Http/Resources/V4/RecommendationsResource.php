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
     *      description="Recommendations Resource",
     *
     *     @OA\Property(
     *          property="data",
     *          type="array",
     *
     *          @OA\Items(
     *              type="object",
     *              @OA\Property(
     *                  property="mal_id",
     *                  type="integer",
     *                  description="Recommended MyAnimeList ID"
     *              ),
     *              @OA\Property(
     *                  property="url",
     *                  type="string",
     *                  description="Recommended MyAnimeList URL"
     *              ),
     *              @OA\Property(
     *                  property="image_url",
     *                  type="string",
     *                  description="Recommended MyAnimeList Image URL"
     *              ),
     *              @OA\Property(
     *                  property="recommendation_url",
     *                  type="string",
     *                  description="Recommendation MyAnimeList URL"
     *              ),
     *              @OA\Property(
     *                  property="title",
     *                  type="string",
     *                  description="Recommended Entry Title"
     *              ),
     *              @OA\Property(
     *                  property="recommendation_count",
     *                  type="integer",
     *                  description="Number of users who have recommended this entry"
     *              ),
     *          ),
     *     ),
     *  ),
     *
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
     *
     *              @OA\Property(
     *                  property="entry",
     *                  type="object",
     *
     *                  @OA\Property(
     *                      property="mal_id",
     *                      type="integer",
     *                      description="Recommended MyAnimeList ID"
     *                  ),
     *                  @OA\Property(
     *                      property="url",
     *                      type="string",
     *                      description="Recommended MyAnimeList URL"
     *                  ),
     *                  @OA\Property(
     *                      property="images",
     *                      type="object",
     *                      description="Recommended MyAnimeList Image URL",
     *                      @OA\Schema(
     *                          ref="#/components/schemas/anime images"
     *                      ),
     *                  ),
     *                  @OA\Property(
     *                      property="title",
     *                      type="string",
     *                      description="Recommended Entry Title"
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