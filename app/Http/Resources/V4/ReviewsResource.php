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
     *              ref="#/components/schemas/anime review"
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
        return $this['results'];
    }
}