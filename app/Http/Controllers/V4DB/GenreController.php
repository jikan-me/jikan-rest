<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Http\Resources\V4\AnimeCollection;
use Illuminate\Http\Request;
use Jikan\Request\Genre\AnimeGenreRequest;
use Jikan\Request\Genre\AnimeGenresRequest;
use Jikan\Request\Genre\MangaGenreRequest;

class GenreController extends Controller
{

    private $request;
    const MAX_RESULTS_PER_PAGE = 50;

    public function anime(Request $request, int $id)
    {
        $this->request = $request;
        $page = $this->request->get('page') ?? 1;

        $results = Anime::query()
            ->where('genres.mal_id', $id)
            ->orderBy('title');

        $results = $results
            ->paginate(
                self::MAX_RESULTS_PER_PAGE,
                ['*'],
                null,
                $page
            );

        return new AnimeCollection(
            $results
        );
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
