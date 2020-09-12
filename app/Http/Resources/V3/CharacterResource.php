<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class CharacterResource extends JsonResource
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
            'mal_id' => $this->mal_id,
            'url' => $this->url,
            'image_url' => $this->images['jpg']['image_url'],
            'name' => $this->name,
            'nicknames' => $this->nicknames,
            'member_favorites' => $this->favorites,
            'about' => $this->about,
            'animeography' => $this->bcAnimeography($this->animeography),
            'mangaography' => $this->bcMangaography($this->mangaography),
            'voice_actors' => $this->bcVoiceActors($this->voice_actors)
        ];
    }

    private function bcAnimeography($ography) : array
    {
        foreach ($ography as &$entry) {
            $entry = [
                'mal_id' => $entry['anime']['mal_id'],
                'name' => $entry['anime']['title'],
                'url' => $entry['anime']['url'],
                'image_url' => $entry['anime']['images']['jpg']['image_url'],
                'role' => $entry['role'],
            ];
        }

        return $ography;
    }

    private function bcMangaography($ography) : array
    {
        foreach ($ography as &$entry) {
            $entry = [
                'mal_id' => $entry['manga']['mal_id'],
                'name' => $entry['manga']['title'],
                'url' => $entry['manga']['url'],
                'image_url' => $entry['manga']['images']['jpg']['image_url'],
                'role' => $entry['role'],
            ];
        }

        return $ography;
    }

    private function bcVoiceActors($voiceActors) : array
    {
        foreach ($voiceActors as &$person) {
            $person = [
                'mal_id' => $person['person']['mal_id'],
                'name' => $person['person']['name'],
                'url' => $person['person']['url'],
                'image_url' => $person['person']['images']['jpg']['image_url'],
                'language' => $person['language'],
            ];
        }

        return $voiceActors;
    }

}