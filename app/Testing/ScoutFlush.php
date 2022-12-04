<?php

namespace App\Testing;

trait ScoutFlush
{
    public function runScoutFlush(): void
    {
        $models = ["App\\Anime", "App\\Manga", "App\\Character", "App\\GenreAnime", "App\\GenreManga", "App\\Person"];
        foreach ($models as $model) {
            $this->artisan("scout:flush", ["model" => $model]);
        }
    }
}
