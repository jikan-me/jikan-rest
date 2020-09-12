<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class AnimeCharactersStaffResource extends JsonResource
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
            'characters' => $this->bcCharacters($this['characters']),
            'staff' => $this['staff'],
        ];
    }

    private function bcCharacters($characters) : array
    {
        $bcCharacters = [];
        foreach ($characters as $key => $character) {
            $bcCharacters[$key] = $character;

            foreach ($bcCharacters[$key]['voice_actors'] as &$voiceActor) {
                $voiceActor = [
                    'mal_id' => $voiceActor['person']['mal_id'],
                    'name' => $voiceActor['person']['name'],
                    'url' => $voiceActor['person']['url'],
                    'image_url' => $voiceActor['person']['images']['jpg']['image_url'],
                    'language' => $voiceActor['language'],
                ];
            }
        }

        return $bcCharacters;
    }
}