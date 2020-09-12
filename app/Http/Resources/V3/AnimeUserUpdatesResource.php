<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class AnimeUserUpdatesResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'users' => $this->bcUsers($this['users']),
        ];
    }

    private function bcUsers($users) : array
    {
        foreach ($users as &$user) {
            $user = [
                'username' => $user['username'],
                'url' => $user['url'],
                'image_url' => $user['images']['jpg']['image_url'],
                'score' => $user['score'],
                'status' => $user['status'],
                'episodes_seen' => $user['episodes_seen'],
                'episodes_total' => $user['episodes_total'],
                'date' => $user['date'],
            ];
        }

        return $users;
    }
}