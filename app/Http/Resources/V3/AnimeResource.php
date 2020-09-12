<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class AnimeResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this::withoutWrapping();

        return [
            'mal_id' => $this->mal_id,
            'url' => $this->url,
            'image_url' => $this->images['jpg']['image_url'],
            'trailer_url' => $this->trailer['embed_url'],
            'title' => $this->title,
            'title_english' => $this->title_english,
            'title_japanese' => $this->title_japanese,
            'title_synonyms' => $this->title_synonyms,
            'type' => $this->type,
            'source' => $this->source,
            'episodes' => $this->episodes,
            'status' => $this->status,
            'airing' => $this->airing,
            'aired' => $this->aired,
            'duration' => $this->duration,
            'rating' => $this->rating,
            'score' => $this->score,
            'scored_by' => $this->scored_by,
            'rank' => $this->rank,
            'popularity' => $this->popularity,
            'members' => $this->members,
            'favorites' => $this->favorites,
            'synopsis' => $this->synopsis,
            'background' => $this->background,
            'season' => $this->season,
            'year' => $this->year,
            'broadcast' => $this->broadcast['string'],
            'related' => $this->bcRelations($this->related),
            'producers' => $this->producers,
            'licensors' => $this->licensors,
            'studios' => $this->studios,
            'genres' => $this->genres,
            'opening_themes' => $this->themes['openings'],
            'ending_themes' => $this->themes['endings'],
        ];
    }

    /**
     * Backwards Compatability Methods
     */

    /**
     * @param $relations
     * @return array
     */
    private function bcRelations($relations) : array
    {
        if (empty($relations)) {
            return [];
        }

        $related = [];
        foreach ($relations as $relation) {
            $related[$relation['relation']] = $relation['items'];
        }

        return $related;
    }
}