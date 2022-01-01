<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class AnimeEpisodeResource extends JsonResource
{
    /**
     *  @OA\Schema(
     *      schema="anime episode",
     *      description="Anime Episode Resource",
     *
     *     @OA\Property(
     *          property="data",
     *          type="object",
     *          @OA\Property(
     *              property="mal_id",
     *              type="integer",
     *              description="MyAnimeList ID"
     *          ),
     *          @OA\Property(
     *              property="url",
     *              type="string",
     *              description="MyAnimeList URL"
     *          ),
     *          @OA\Property(
     *              property="title",
     *              type="string",
     *              description="Title"
     *          ),
     *          @OA\Property(
     *              property="title_japanese",
     *              type="string",
     *              description="Title Japanese"
     *          ),
     *          @OA\Property(
     *              property="title_romanji",
     *              type="string",
     *              description="title_romanji"
     *          ),
     *          @OA\Property(
     *              property="duration",
     *              type="integer",
     *              description="Episode duration in seconds"
     *          ),
     *          @OA\Property(
     *              property="aired",
     *              type="string",
     *              description="Aired Date ISO8601"
     *          ),
     *          @OA\Property(
     *              property="filler",
     *              type="boolean",
     *              description="Filler episode"
     *          ),
     *          @OA\Property(
     *              property="recap",
     *              type="boolean",
     *              description="Recap episode"
     *          ),
     *          @OA\Property(
     *              property="synopsis",
     *              type="string",
     *              description="Episode Synopsis"
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
        return [
            'mal_id' => $this['mal_id'],
            'url' => $this['url'],
            'title' => $this['title'],
            'title_japanese' => $this['title_japanese'],
            'title_romanji' => $this['title_romanji'],
            'duration' => $this['duration'],
            'aired' => $this['aired'],
            'filler' => $this['filler'],
            'recap' => $this['recap'],
            'synopsis' => $this['synopsis']
        ];
    }
}