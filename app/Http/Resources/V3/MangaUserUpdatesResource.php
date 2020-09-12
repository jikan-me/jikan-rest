<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class MangaUserUpdatesResource extends JsonResource
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
                'volumes_read' => $user['volumes_read'],
                'volumes_total' => $user['volumes_total'],
                'chapters_read' => $user['chapters_read'],
                'chapters_total' => $user['chapters_total'],
                'date' => $user['date'],
            ];
        }

        return $users;
    }
}