<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class MangaResource extends JsonResource
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
            'title' => $this->title,
            'title_english' => $this->title_english,
            'title_japanese' => $this->title_japanese,
            'title_synonyms' => $this->title_synonyms,
            'type' => $this->type,
            'chapters' => $this->chapters,
            'volumes' => $this->volumes,
            'publishing' => $this->publishing,
            'published' => $this->published,
            'scored' => $this->score,
            'scored_by' => $this->scored_by,
            'rank' => $this->rank,
            'popularity' => $this->popularity,
            'members' => $this->members,
            'favorites' => $this->favorites,
            'synopsis' => $this->synopsis,
            'background' => $this->background,
            'related' => $this->bcRelations($this->related),
            'authors' => $this->authors,
            'serializations' => $this->serializations,
            'genres' => $this->genres,
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