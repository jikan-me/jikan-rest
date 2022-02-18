<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class ClubRelationsResource extends JsonResource
{

    /**
     *  @OA\Schema(
     *      schema="club_relations",
     *      description="Club Relations",
     *
     *     @OA\Property(
     *          property="data",
     *          type="object",
     *
     *          @OA\Property(
     *              property="anime",
     *              type="array",
     *              @OA\Items(
     *                  ref="#/components/schemas/mal_url"
     *              ),
     *          ),
     *
     *          @OA\Property(
     *              property="manga",
     *              type="array",
     *              @OA\Items(
     *                  ref="#/components/schemas/mal_url"
     *              ),
     *          ),
     *
     *          @OA\Property(
     *              property="characters",
     *              type="array",
     *              @OA\Items(
     *                  ref="#/components/schemas/mal_url"
     *              ),
     *          ),
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
        return [
            'anime' => $this['anime'],
            'manga' => $this['manga'],
            'characters' => $this['characters'],
        ];
    }
}