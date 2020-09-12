<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class AnimeNewsResource extends JsonResource
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
            'articles' => $this->bcArticles($this['results'])
        ];
    }

    private function bcArticles($articles) : array
    {
        foreach ($articles as &$article) {
            $article = [
                'url' => $article['url'],
                'title' => $article['title'],
                'date' => $article['date'],
                'author_name' => $article['author_username'],
                'author_url' => $article['author_url'],
                'forum_url' => $article['forum_url'],
                'image_url' => $article['images']['jpg']['image_url'],
                'comments' => $article['comments'],
                'intro' => $article['excerpt'],
            ];
        }

        return $articles;
    }
}