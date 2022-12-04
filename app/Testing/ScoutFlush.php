<?php

namespace App\Testing;

trait ScoutFlush
{
    public function runScoutFlush(): void
    {
        $this->artisan("scout:flush App\\Anime");
        $this->artisan("scout:flush App\\Manga");
        $this->artisan("scout:flush App\\Character");
        $this->artisan("scout:flush App\\GenreAnime");
        $this->artisan("scout:flush App\\GenreManga");
        $this->artisan("scout:flush App\\Person");
    }
}
