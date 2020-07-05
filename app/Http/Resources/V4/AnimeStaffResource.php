<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class AnimeStaffResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this['staff'];
    }
}