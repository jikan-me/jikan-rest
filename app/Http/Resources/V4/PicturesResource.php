<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class PicturesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     *  @OA\Schema(
     *      schema="pictures",
     *      description="Pictures Resource",
     *
     *     @OA\Property(
     *          property="data",
     *          type="array",
     *
     *          @OA\Items(
     *              type="object",
     *              @OA\Property(
     *                  property="images",
     *                  ref="#/components/schemas/anime_images"
     *              ),
     *          ),
     *     ),
     *  ),
     *
     *  @OA\Schema(
     *      schema="pictures_variants",
     *      description="Pictures Resource",
     *
     *     @OA\Property(
     *          property="data",
     *          type="array",
     *
     *          @OA\Items(
     *              type="object",
     *              @OA\Property(
     *                  property="images",
     *                  ref="#/components/schemas/common_images"
     *              ),
     *          ),
     *     ),
     *  )
     */
    public function toArray($request)
    {
        return $this['pictures'];
    }
}