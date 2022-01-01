<?php

namespace App\Http\Controllers\V4;

use Jikan\Request\Genre\AnimeGenreRequest;
use Jikan\Request\Genre\AnimeGenresRequest;
use Jikan\Request\Genre\MangaGenreRequest;

class GenreController extends Controller
{
    public function anime(int $id, int $page = 1)
    {
        $person = $this->jikan->getAnimeGenre(new AnimeGenreRequest($id, $page));
        return response($this->serializer->serialize($person, 'json'));
    }

    public function manga(int $id, int $page = 1)
    {
        $person = $this->jikan->getMangaGenre(new MangaGenreRequest($id, $page));
        return response($this->serializer->serialize($person, 'json'));
    }

    public function animeListing()
    {
        $results = $this->jikan->getAnimeGenres(new AnimeGenresRequest());
        return response($this->serializer->serialize($results, 'json'));
    }

    public function mangaListing()
    {
        $results = $this->jikan->getAnimeGenres(new AnimeGenresRequest());
        return response($this->serializer->serialize($results, 'json'));
    }
}
