<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Request;
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
     *          @OA\Property(
     *              property="anime",
     *              type="array",
     *
     *              @OA\Items(
     *                  ref="#/components/schemas/mal_url"
     *              ),
     *          ),
     *
     *          @OA\Property(
     *              property="manga",
     *              type="array",
     *
     *              @OA\Items(
     *                  ref="#/components/schemas/mal_url"
     *              ),
     *          ),
     *
     *          @OA\Property(
     *              property="characters",
     *              type="array",
     *
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
     * @param  Request  $request
     * @return array<string, mixed>
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
