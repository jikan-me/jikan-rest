<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class AnimeRelationsResource extends JsonResource
{

    /**
     *  @OA\Schema(
     *      schema="anime relations",
     *      description="Anime Relations",
     *
     *     @OA\Property(
     *          property="data",
     *          type="array",
     *          @OA\Items(
     *              type="object",
     *
     *              @OA\Property(
     *                  property="relation",
     *                  type="string",
     *                  description="Relation type"
     *              ),
     *              @OA\Property(
     *                  property="entry",
     *                  type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      ref="#/components/schemas/mal_url"
     *                  ),
     *              )
     *          )
     *     )
     * )
     */

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this['related'];
    }
}