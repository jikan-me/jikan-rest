<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class AnimeEpisodesResource extends JsonResource
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
            'last_visible_page' => $this['episodes_last_page'],
            'has_next_page' => $this['has_next_page'] ?? false,
            'episodes' => $this['episodes']
        ];
    }
}