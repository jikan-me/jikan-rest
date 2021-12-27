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
     *          type="object",
     *
     *          @OA\Property(
     *               property="jpg",
     *               type="array",
     *               @OA\Items(
     *                   type="object",
     *                   @OA\Property(
     *                       property="image_url",
     *                       type="string",
     *                       description="Default JPG Image Size URL"
     *                   ),
     *              ),
     *          ),
     *     ),
     *  ),
     *
     *  @OA\Schema(
     *      schema="pictures variants",
     *      description="Pictures Resource",
     *
     *     @OA\Property(
     *          property="data",
     *          type="object",
     *
     *          @OA\Property(
     *               property="jpg",
     *               type="array",
     *               @OA\Items(
     *                   type="object",
     *                   @OA\Property(
     *                       property="image_url",
     *                       type="string",
     *                       description="Default JPG Image Size URL"
     *                   ),
     *                   @OA\Property(
     *                       property="large_image_url",
     *                       type="string",
     *                       description="Large JPG Image Size URL"
     *                   ),
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