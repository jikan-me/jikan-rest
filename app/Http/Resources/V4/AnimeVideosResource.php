<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class AnimeVideosResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     *  @OA\Schema(
     *      schema="anime videos",
     *      description="Anime Videos Resource",
     *
     *     @OA\Property(
     *          property="data",
     *          type="object",
     *
     *          @OA\Property(
     *               property="promos",
     *               type="array",
     *               @OA\Items(
     *                   type="object",
     *
     *                   @OA\Property(
     *                       property="title",
     *                       type="string",
     *                       description="Title"
     *                   ),
     *                   @OA\Property(
     *                       property="trailer",
     *                       ref="#/components/schemas/trailer"
     *                   ),
     *              ),
     *          ),
     *          @OA\Property(
     *               property="episodes",
     *               type="array",
     *
     *               @OA\Items(
     *                   type="object",
     *                   @OA\Property(
     *                       property="mal_id",
     *                       type="integer",
     *                       description="MyAnimeList ID"
     *                   ),
     *                   @OA\Property(
     *                       property="url",
     *                       type="string",
     *                       description="MyAnimeList URL"
     *                   ),
     *                   @OA\Property(
     *                       property="title",
     *                       type="string",
     *                       description="Title"
     *                   ),
     *                   @OA\Property(
     *                       property="episode",
     *                       type="string",
     *                       description="Episode"
     *                   ),
     *                   @OA\Property(
     *                       property="images",
     *                       type="object",
     *                       ref="#/components/schemas/common images"
     *                   ),
     *               ),
     *          ),
     *     ),
     *  )
     */
    public function toArray($request)
    {
        return [
            'promo' => $this['promo'],
            'episodes' => $this['episodes']
        ];
    }
}