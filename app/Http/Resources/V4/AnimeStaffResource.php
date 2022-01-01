<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class AnimeStaffResource extends JsonResource
{

    /**
     *  @OA\Schema(
     *      schema="anime staff",
     *      description="Anime Staff Resource",
     *
     *     @OA\Property(
     *          property="data",
     *          type="array",
     *
     *          @OA\Items(
     *               type="object",
     *
     *              @OA\Property(
     *                  property="person",
     *                  type="object",
     *                  description="Person details",
     *
     *                  @OA\Property(
     *                      property="mal_id",
     *                      type="integer",
     *                      description="MyAnimeList ID"
     *                  ),
     *                  @OA\Property(
     *                      property="url",
     *                      type="string",
     *                      description="MyAnimeList URL"
     *                  ),
     *                  @OA\Property(
     *                      property="images",
     *                      type="string",
     *                      ref="#/components/schemas/people images"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      description="Name"
     *                  ),
     *              ),
     *              @OA\Property(
     *                  property="positions",
     *                  type="array",
     *                  description="Staff Positions",
     *                  @OA\Items(type="string")
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
        return $this['staff'];
    }
}