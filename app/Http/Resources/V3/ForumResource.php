<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class ForumResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'topics' => $this->bcTopics($this['topics'])
        ];
    }

    private function bcTopics($topics) : array
    {
        foreach ($topics as &$topic) {
            $topic = [
                'topic_id' => $topic['mal_id'],
                'url' => $topic['url'],
                'title' => $topic['title'],
                'date_posted' => $topic['date'],
                'author_name' => $topic['author_username'],
                'author_url' => $topic['author_url'],
                'replies' => $topic['comments'],
                'last_post' => [
                    'url' => $topic['url'],
                    'author_name' => $topic['author_username'],
                    'author_url' => $topic['author_url'],
                    'date_posted' => $topic['date'],
                ],
            ];
        }

        return $topics;
    }
}