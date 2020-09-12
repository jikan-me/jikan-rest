<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class AnimeVideosResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'promo' => $this->bcPromos($this['promo']),
            'episodes' => $this->bcEpisodes($this['episodes'])
        ];
    }

    private function bcPromos($promos) : array
    {
        foreach ($promos as &$promo) {
            $promo = [
                'title' => $promo['title'],
                'image_url' => $promo['trailer']['images']['medium_image_url'],
                'video_url' => $promo['trailer']['embed_url'],
            ];
        }

        return $promos;
    }

    private function bcEpisodes($episodes) : array
    {
        foreach ($episodes as &$episode) {
            $episode = [
                'title' => $episode['title'],
                'episode' => $episode['episode'],
                'url' => $episode['url'],
                'image_url' => $episode['images']['jpg']['image_url'],
            ];
        }

        return $episodes;
    }
}