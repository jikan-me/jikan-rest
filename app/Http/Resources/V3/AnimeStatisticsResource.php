<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class AnimeStatisticsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'watching' => $this['watching'],
            'completed' => $this['completed'],
            'on_hold' => $this['on_hold'],
            'dropped' => $this['dropped'],
            'plan_to_watch' => $this['plan_to_watch'],
            'total' => $this['total'],
            'scores' => $this->bcScores($this['scores'])
        ];
    }

    private function bcScores($scores) : array
    {
        foreach ($scores as &$score) {
            unset($score['score']);
        }

        return $scores;
    }
}