<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileStatisticsResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'anime' => $this->anime_stats,
            'manga' => $this->manga_stats,
        ];
    }
}