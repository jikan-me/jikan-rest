<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewsResource extends JsonResource
{
    /**
     *
     * @OA\Schema(
     *     schema="manga_review",
     *     type="object",
     *     @OA\Property(
     *         property="mal_id",
     *         type="integer",
     *         description="MyAnimeList ID"
     *     ),
     *     @OA\Property(
     *         property="url",
     *         type="string",
     *         description="MyAnimeList review URL"
     *     ),
     *     @OA\Property(
     *         property="type",
     *         type="string",
     *         description="Entry type"
     *     ),
     *     @OA\Property(
     *         property="reactions",
     *         type="object",
     *         description="User reaction count on the review",
     *         @OA\Property(
     *             property="overall",
     *             type="integer",
     *             description="Overall reaction count"
     *         ),
     *         @OA\Property(
     *             property="nice",
     *             type="integer",
     *             description="Nice reaction count"
     *         ),
     *         @OA\Property(
     *             property="love_it",
     *             type="integer",
     *             description="Love it reaction count"
     *         ),
     *         @OA\Property(
     *             property="funny",
     *             type="integer",
     *             description="Funny reaction count"
     *         ),
     *         @OA\Property(
     *             property="confusing",
     *             type="integer",
     *             description="Confusing reaction count"
     *         ),
     *         @OA\Property(
     *             property="informative",
     *             type="integer",
     *             description="Informative reaction count"
     *         ),
     *         @OA\Property(
     *             property="well_written",
     *             type="integer",
     *             description="Well written reaction count"
     *         ),
     *         @OA\Property(
     *             property="creative",
     *             type="integer",
     *             description="Creative reaction count"
     *         )
     *     ),
     *     @OA\Property(
     *         property="date",
     *         type="string",
     *         description="Review created date ISO8601"
     *     ),
     *     @OA\Property(
     *         property="review",
     *         type="string",
     *         description="Review content"
     *     ),
     *     @OA\Property(
     *         property="score",
     *         type="integer",
     *         description="Number of user votes on the Review"
     *     ),
     *     @OA\Property (
     *         property="tags",
     *         type="array",
     *         description="Review tags",
     *         @OA\Items(type="string"),
     *     ),
     *     @OA\Property (
     *         property="is_spoiler",
     *         type="boolean",
     *         description="The review contains spoiler"
     *     ),
     *     @OA\Property (
     *         property="is_preliminary",
     *         type="boolean",
     *         description="The review was made before the entry was completed"
     *     ),
     *  ),
     *
     * @OA\Schema(
     *     schema="anime_review",
     *     type="object",
     *     @OA\Property(
     *         property="mal_id",
     *         type="integer",
     *         description="MyAnimeList ID"
     *     ),
     *     @OA\Property(
     *         property="url",
     *         type="string",
     *         description="MyAnimeList review URL"
     *     ),
     *     @OA\Property(
     *         property="type",
     *         type="string",
     *         description="Entry type"
     *     ),
     *     @OA\Property(
     *         property="reactions",
     *         type="object",
     *         description="User reaction count on the review",
     *         @OA\Property(
     *             property="overall",
     *             type="integer",
     *             description="Overall reaction count"
     *         ),
     *         @OA\Property(
     *             property="nice",
     *             type="integer",
     *             description="Nice reaction count"
     *         ),
     *         @OA\Property(
     *             property="love_it",
     *             type="integer",
     *             description="Love it reaction count"
     *         ),
     *         @OA\Property(
     *             property="funny",
     *             type="integer",
     *             description="Funny reaction count"
     *         ),
     *         @OA\Property(
     *             property="confusing",
     *             type="integer",
     *             description="Confusing reaction count"
     *         ),
     *         @OA\Property(
     *             property="informative",
     *             type="integer",
     *             description="Informative reaction count"
     *         ),
     *         @OA\Property(
     *             property="well_written",
     *             type="integer",
     *             description="Well written reaction count"
     *         ),
     *         @OA\Property(
     *             property="creative",
     *             type="integer",
     *             description="Creative reaction count"
     *         )
     *     ),
     *     @OA\Property(
     *         property="date",
     *         type="string",
     *         description="Review created date ISO8601"
     *     ),
     *     @OA\Property(
     *         property="review",
     *         type="string",
     *         description="Review content"
     *     ),
     *     @OA\Property(
     *         property="score",
     *         type="integer",
     *         description="Number of user votes on the Review"
     *     ),
     *     @OA\Property (
     *         property="tags",
     *         type="array",
     *         description="Review tags",
     *         @OA\Items(type="string"),
     *     ),
     *     @OA\Property (
     *         property="is_spoiler",
     *         type="boolean",
     *         description="The review contains spoiler"
     *     ),
     *     @OA\Property (
     *         property="is_preliminary",
     *         type="boolean",
     *         description="The review was made before the entry was completed"
     *     ),
     *     @OA\Property(
     *         property="episodes_watched",
     *         type="integer",
     *         description="Number of episodes watched"
     *     ),
     *  ),
     *
     *
     *  @OA\Schema(
     *      schema="anime_reviews",
     *      description="Anime Reviews Resource",
     *
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/pagination"),
     *          @OA\Schema(
     *              @OA\Property(
     *                   property="data",
     *                   type="array",
     *
     *                   @OA\Items(
     *                      type="object",
     *
     *                      allOf={
     *                          @OA\Schema(ref="#/components/schemas/anime_review"),
     *                          @OA\Schema(
     *                              @OA\Property(
     *                                  property="user",
     *                                  type="object",
     *                                  ref="#/components/schemas/user_meta"
     *                              ),
     *                          ),
     *                      },
     *                   ),
     *              ),
     *          ),
     *      },
     *  ),
     *
     *
     *  @OA\Schema(
     *      schema="manga_reviews",
     *      description="Manga Reviews Resource",
     *
     *
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/pagination"),
     *          @OA\Schema(
     *              @OA\Property(
     *                   property="data",
     *                   type="array",
     *
     *                   @OA\Items(
     *                      type="object",
     *
     *                      allOf={
     *                          @OA\Schema(ref="#/components/schemas/manga_review"),
     *                          @OA\Schema(
     *                              @OA\Property(
     *                                  property="user",
     *                                  type="object",
     *                                  ref="#/components/schemas/user_meta"
     *                              ),
     *                          ),
     *                      },
     *                   ),
     *              ),
     *          ),
     *      },
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
                'last_visible_page' => $this['last_visible_page'],
                'has_next_page' => $this['has_next_page'],
            ],
            'data' => $this['results'],
        ];
    }
}
