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
     *                          @OA\Schema(ref="#/components/schemas/anime review"),
     *                          @OA\Schema(ref="#/components/schemas/user meta"),
     *                      },
     *                   ),
     *              ),
     *          ),
     *      },
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
     *              ref="#/components/schemas/manga review"
     *          ),
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
                'last_visible_page' => $this['last_visible_page'],
                'has_next_page' => $this['has_next_page'],
            ],
            'data' => $this['results'],
        ];
    }
}