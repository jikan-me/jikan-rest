<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class AnimeEpisodeResource extends JsonResource
{
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