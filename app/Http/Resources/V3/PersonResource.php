<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
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
            'website_url' => $this->website_url,
            'image_url' => $this->images['jpg']['image_url'],
            'name' => $this->name,
            'given_name' => $this->given_name,
            'family_name' => $this->family_name,
            'alternate_names' => $this->alternate_names,
            'birthday' => $this->birthday,
            'member_favorites' => $this->favorites,
            'about' => $this->about,
            'voice_acting_roles' => $this->bcVoiceActingRoles($this->voice_acting_roles),
            'anime_staff_positions' => $this->bcAnimeStaffPositions($this->anime_staff_positions),
            'published_manga' => $this->bcPublishedManga($this->published_manga),
        ];
    }

    private function bcVoiceActingRoles($voiceActingRoles) : array
    {
        foreach ($voiceActingRoles as &$voiceActor) {
            $voiceActor = [
                'role' => $voiceActor['role'],
                'anime' => [
                    'mal_id' => $voiceActor['anime']['mal_id'],
                    'url' => $voiceActor['anime']['url'],
                    'image_url' => $voiceActor['anime']['images']['jpg']['image_url'],
                    'name' => $voiceActor['anime']['title'],
                ],
                'character' => [
                    'mal_id' => $voiceActor['character']['mal_id'],
                    'url' => $voiceActor['character']['url'],
                    'image_url' => $voiceActor['character']['images']['jpg']['image_url'],
                    'name' => $voiceActor['character']['name'],
                ],
            ];
        }

        return $voiceActingRoles;
    }

    private function bcAnimeStaffPositions($animeStaffPositions) : array
    {
        foreach ($animeStaffPositions as &$anime) {
            $anime = [
                'position' => $anime['position'],
                'anime' => [
                    'mal_id' => $anime['anime']['mal_id'],
                    'url' => $anime['anime']['url'],
                    'image_url' => $anime['anime']['images']['jpg']['image_url'],
                    'name' => $anime['anime']['title'],
                ],
            ];
        }

        return $animeStaffPositions;
    }

    private function bcPublishedManga($publishedManga) : array
    {
        foreach ($publishedManga as &$manga) {
            $manga = [
                'position' => $manga['position'],
                'manga' => [
                    'mal_id' => $manga['manga']['mal_id'],
                    'url' => $manga['manga']['url'],
                    'image_url' => $manga['manga']['images']['jpg']['image_url'],
                    'name' => $manga['manga']['title'],
                ],
            ];
        }

        return $publishedManga;
    }
}