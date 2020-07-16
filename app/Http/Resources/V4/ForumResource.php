<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class ForumResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     *  @OA\Schema(
     *      schema="forum",
     *      description="Forum Resource",
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
     *                  property="title",
     *                  type="string",
     *                  description="Title"
     *              ),
     *              @OA\Property(
     *                  property="date",
     *                  type="string",
     *                  description="Post Date ISO8601"
     *              ),
     *              @OA\Property(
     *                  property="author_username",
     *                  type="string",
     *                  description="Author MyAnimeList Username"
     *              ),
     *              @OA\Property(
     *                  property="author_url",
     *                  type="string",
     *                  description="Author Profile URL"
     *              ),
     *              @OA\Property(
     *                  property="comments",
     *                  type="integer",
     *                  description="Comment count"
     *              ),
     *              @OA\Property(
     *                  property="last_comment",
     *                  type="object",
     *                  description="Last comment details",
     *                  @OA\Property(
     *                      property="url",
     *                      type="string",
     *                      description="Last comment URL"
     *                  ),
     *                  @OA\Property(
     *                      property="author_username",
     *                      type="string",
     *                      description="Author MyAnimeList Username"
     *                  ),
     *                  @OA\Property(
     *                      property="author_url",
     *                      type="string",
     *                      description="Author Profile URL"
     *                  ),
     *                  @OA\Property(
     *                      property="date",
     *                      type="string",
     *                      description="Last comment date posted ISO8601"
     *                  ),
     *              ),
     *          ),
     *     ),
     *  )
     */
    public function toArray($request)
    {
        return $this['topics'];
    }
}