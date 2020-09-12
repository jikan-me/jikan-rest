<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class MangaReviewsResource extends JsonResource
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
            'reviews' => $this->bcReviews($this['results']),
        ];
    }

    private function bcReviews($reviews) : array
    {
        foreach ($reviews as &$review) {
            $review = [
                'mal_id' => $review['mal_id'],
                'url' => $review['url'],
                'type' => $review['type'],
                'helpful_count' => $review['votes'],
                'date' => $review['date'],
                'reviewer' => [
                    'url' => $review['user']['url'],
                    'image_url' => $review['user']['images']['jpg']['image_url'],
                    'username' => $review['user']['username'],
                    'chapters_read' => $review['chapters_read'],
                    'scores' => $review['scores'],
                ],
                'content' => $review['review'],
            ];
        }

        return $reviews;
    }
}