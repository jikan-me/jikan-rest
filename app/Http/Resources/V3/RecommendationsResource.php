<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class RecommendationsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'recommendations' => $this->bcRecommendations($this['recommendations'])
        ];
    }

    private function bcRecommendations($recommendations) : array
    {
        foreach ($recommendations as &$recommendation) {
            $recommendation = [
                'mal_id' => $recommendation['mal_id'],
                'url' => $recommendation['url'],
                'image_url' => $recommendation['images']['jpg']['image_url'],
                'recommendation_url' => $recommendation['recommendation_url'],
                'title' => $recommendation['title'],
                'recommendation_count' => $recommendation['recommendation_count'],
            ];
        }

        return $recommendations;
    }
}