<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Character;
use App\Http\HttpHelper;
use App\Http\HttpResponse;
use App\Http\Resources\V4\AnimeCollection;
use App\Http\Resources\V4\AnimeResource;
use App\Http\Resources\V4\CharacterCollection;
use App\Http\Resources\V4\MangaCollection;
use App\Http\Resources\V4\PersonCollection;
use App\Manga;
use App\Person;
use Illuminate\Http\Request;
use MongoDB\BSON\UTCDateTime;

class RandomController extends Controller
{
    public function anime(Request $request)
    {
        $sfw = $request->get('sfw');


        $results = Anime::query();

        if (!is_null($sfw)) {
            $results = $results
                ->where('rating', '!=', 'Rx - Hentai');
        }

        $results = $results
            ->raw(fn($collection) => $collection->aggregate([ ['$sample' => ['size' => 1]] ]));

        return new AnimeCollection(
            $results
        );
    }

    public function manga(Request $request)
    {
        $sfw = $request->get('sfw');


        $results = Manga::query();

        if (!is_null($sfw)) {
            $results = $results
                ->where('type', '!=', 'Doujinshi');
        }

        $results = $results
            ->raw(fn($collection) => $collection->aggregate([ ['$sample' => ['size' => 1]] ]));

        return new MangaCollection(
            $results
        );
    }

    public function characters(Request $request)
    {

        $results = Character::query()
            ->raw(fn($collection) => $collection->aggregate([ ['$sample' => ['size' => 1]] ]));

        return new CharacterCollection(
            $results
        );
    }

    public function people(Request $request)
    {

        $results = Person::query()
            ->raw(fn($collection) => $collection->aggregate([ ['$sample' => ['size' => 1]] ]));

        return new PersonCollection(
            $results
        );
    }
}
