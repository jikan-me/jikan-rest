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
     *      @OA\Property(
     *          property="related",
     *          ref="#/components/schemas/anime relations"
     *      ),
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
        return $this['related'];
    }
}