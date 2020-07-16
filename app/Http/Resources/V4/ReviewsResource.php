<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewsResource extends JsonResource
{
    /**
     *  @OA\Schema(
     *      schema="anime reviews",
     *      description="Anime Reviews Resource",
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
     *                  description="MyAnimeList ID"
     *              ),
     *              @OA\Property(
     *                  property="url",
     *                  type="string",
     *                  description="MyAnimeList URL"
     *              ),
     *              @OA\Property(
     *                  property="type",
     *                  type="string",
     *                  description="Entry Type"
     *              ),
     *              @OA\Property(
     *                  property="votes",
     *                  type="integer",
     *                  description="Number of user votes on the Review"
     *              ),
     *              @OA\Property(
     *                  property="date",
     *                  type="string",
     *                  description="Review created date ISO8601"
     *              ),
     *              @OA\Property(
     *                  property="scores",
     *                  type="object",
     *                  description="Review Scores breakdown",
     *                  @OA\Property(
     *                      property="overall",
     *                      type="integer",
     *                      description="Overall Score"
     *                  ),
     *                  @OA\Property(
     *                      property="story",
     *                      type="integer",
     *                      description="Story Score"
     *                  ),
     *                  @OA\Property(
     *                      property="animation",
     *                      type="integer",
     *                      description="Animation Score"
     *                  ),
     *                  @OA\Property(
     *                      property="sound",
     *                      type="integer",
     *                      description="Sound Score"
     *                  ),
     *                  @OA\Property(
     *                      property="character",
     *                      type="integer",
     *                      description="Character Score"
     *                  ),
     *                  @OA\Property(
     *                      property="enjoyment",
     *                      type="integer",
     *                      description="Enjoyment Score"
     *                  ),
     *              ),
     *              @OA\Property(
     *                  property="review",
     *                  type="string",
     *                  description="Review content"
     *              ),
     *              @OA\Property(
     *                  property="reviewer",
     *                  type="object",
     *                  description="Reviewer details",
     *                  @OA\Property(
     *                      property="username",
     *                      type="string",
     *                      description="MyAnimeList Username"
     *                  ),
     *                  @OA\Property(
     *                      property="url",
     *                      type="string",
     *                      description="MyAnimeList Profile URL"
     *                  ),
     *                  @OA\Property(
     *                      property="image_url",
     *                      type="string",
     *                      description="User Display Picture Image URL"
     *                  ),
     *                  @OA\Property(
     *                      property="episodes_seen",
     *                      type="integer",
     *                      description="Number of episodes seen"
     *                  ),
     *              ),
     *          ),
     *     ),
     *  ),
     *
     *  @OA\Schema(
     *      schema="manga reviews",
     *      description="Manga Reviews Resource",
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
     *                  description="MyAnimeList ID"
     *              ),
     *              @OA\Property(
     *                  property="url",
     *                  type="string",
     *                  description="MyAnimeList URL"
     *              ),
     *              @OA\Property(
     *                  property="type",
     *                  type="string",
     *                  description="Entry Type"
     *              ),
     *              @OA\Property(
     *                  property="votes",
     *                  type="integer",
     *                  description="Number of user votes on the Review"
     *              ),
     *              @OA\Property(
     *                  property="date",
     *                  type="string",
     *                  description="Review created date ISO8601"
     *              ),
     *              @OA\Property(
     *                  property="scores",
     *                  type="object",
     *                  description="Review Scores breakdown",
     *                  @OA\Property(
     *                      property="overall",
     *                      type="integer",
     *                      description="Overall Score"
     *                  ),
     *                  @OA\Property(
     *                      property="story",
     *                      type="integer",
     *                      description="Story Score"
     *                  ),
     *                  @OA\Property(
     *                      property="art",
     *                      type="integer",
     *                      description="Art Score"
     *                  ),
     *                  @OA\Property(
     *                      property="character",
     *                      type="integer",
     *                      description="Character Score"
     *                  ),
     *                  @OA\Property(
     *                      property="enjoyment",
     *                      type="integer",
     *                      description="Enjoyment Score"
     *                  ),
     *              ),
     *              @OA\Property(
     *                  property="review",
     *                  type="string",
     *                  description="Review content"
     *              ),
     *              @OA\Property(
     *                  property="reviewer",
     *                  type="object",
     *                  description="Reviewer details",
     *                  @OA\Property(
     *                      property="username",
     *                      type="string",
     *                      description="MyAnimeList Username"
     *                  ),
     *                  @OA\Property(
     *                      property="url",
     *                      type="string",
     *                      description="MyAnimeList Profile URL"
     *                  ),
     *                  @OA\Property(
     *                      property="image_url",
     *                      type="string",
     *                      description="User Display Picture Image URL"
     *                  ),
     *                  @OA\Property(
     *                      property="episodes_seen",
     *                      type="integer",
     *                      description="Number of episodes seen"
     *                  ),
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
        return $this['reviews'];
    }
}