<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class MangaStatisticsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'reading' => $this['reading'],
            'completed' => $this['completed'],
            'on_hold' => $this['on_hold'],
            'dropped' => $this['dropped'],
            'plan_to_read' => $this['plan_to_read'],
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