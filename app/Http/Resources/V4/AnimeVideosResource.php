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
     *      schema="anime_videos",
     *      description="Anime Videos Resource",
     *
     *     @OA\Property(
     *          property="data",
     *          type="object",
     *
     *          @OA\Property(
     *               property="promo",
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
     *                       ref="#/components/schemas/common_images"
     *                   ),
     *               ),
     *          ),
     *          @OA\Property(
     *               property="music_videos",
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
     *                       property="video",
     *                       ref="#/components/schemas/trailer"
     *                   ),
     *                  @OA\Property (
     *                      type="object",
     *                      property="meta",
     *
     *                      @OA\Property (
     *                          property="title",
     *                          type="string",
     *                          nullable=true
     *                      ),
     *                      @OA\Property (
     *                          property="author",
     *                          type="string",
     *                          nullable=true
     *                      ),
     *                  ),
     *              ),
     *          ),
     *     ),
     *  )
     */
    public function toArray($request)
    {
        return [
            'promo' => $this['promo'],
            'episodes' => $this['episodes'],
            'music_videos' => $this['music_videos'],
        ];
    }
}
