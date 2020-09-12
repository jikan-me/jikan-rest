<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class AnimeEpisodesResource extends JsonResource
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
            'episodes_last_page' => $this['last_visible_page'] ?? 1,
            'episodes' => $this->bcEpisodes($this['results'])
        ];
    }

    private function bcEpisodes($episodes) : array
    {
        foreach ($episodes as &$episode) {
            $episode = [
                'episode_id' => $episode['mal_id'],
                'title' => $episode['title'],
                'title_japanese' => $episode['title_japanese'],
                'title_romanji' => $episode['title_romanji'],
                'aired' => $episode['aired'],
                'filler' => $episode['filler'],
                'recap' => $episode['recap'],
                'video_url' => $episode['url'],
                'forum_url' => $episode['forum_url'],
            ];
        }

        return $episodes;
    }
}