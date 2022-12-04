<?php

namespace App\Testing;

trait ScoutFlush
{
    protected array $searchIndexModelCleanupList = [
        "App\\Anime", "App\\Manga", "App\\Character", "App\\GenreAnime", "App\\GenreManga", "App\\Person"
    ];

    public function runScoutFlush(): void
    {
        foreach ($this->searchIndexModelCleanupList as $model) {
            $this->artisan("scout:flush", ["model" => $model]);
        }
    }
}
