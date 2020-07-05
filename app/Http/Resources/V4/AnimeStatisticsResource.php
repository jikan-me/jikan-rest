<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;

class AnimeStatisticsResource extends JsonResource
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
            'watching' => $this['watching'],
            'completed' => $this['completed'],
            'on_hold' => $this['on_hold'],
            'dropped' => $this['dropped'],
            'plan_to_watch' => $this['plan_to_watch'],
            'total' => $this['total'],
            'scores' => $this['scores']
        ];
    }
}