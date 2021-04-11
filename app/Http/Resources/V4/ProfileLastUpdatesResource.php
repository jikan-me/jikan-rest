<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileLastUpdatesResource extends JsonResource
{

    public function toArray($request)
    {
        return $this->last_updates;
    }
}