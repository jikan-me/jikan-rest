<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class NewsResource extends JsonResource
{
    /**
     *  @OA\Schema(
     *     schema="news",
     *     type="object",
     *     @OA\Property(
     *          property="data",
     *          type="array",
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
     *                  property="forum_url",
     *                  type="string",
     *                  description="Forum topic URL"
     *              ),
     *              @OA\Property(
     *                  property="images",
     *                  type="object",
     *                  ref="#/components/schemas/common images"
     *              ),
     *              @OA\Property(
     *                  property="comments",
     *                  type="integer",
     *                  description="Comment count"
     *              ),
     *              @OA\Property(
     *                  property="excerpt",
     *                  type="string",
     *                  description="Excerpt"
     *              ),
     *         ),
     *     ),
     *  ),
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
            'pagination' => [
                'last_visible_page' => $this['last_visible_page'] ?? 1,
                'has_next_page' => $this['has_next_page'] ?? false,
            ],
            'data' => $this['results'],
        ];
    }
}