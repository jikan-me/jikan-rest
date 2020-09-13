<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileFavoritesResource extends JsonResource
{

    public function toArray($request)
    {
        return $this->favorites;
    }
}