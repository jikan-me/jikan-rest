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
use App\Http\Resources\V4\ResultsResource;
use App\Http\Resources\V4\UserCollection;
use App\Manga;
use App\Person;
use App\Profile;
use App\User;
use Illuminate\Http\Request;
use MongoDB\BSON\UTCDateTime;

class RandomController extends Controller
{
    /**
     *  @OA\Schema(
     *      schema="random",
     *      description="Random Resources",
     *
     *              @OA\Property(
     *                   property="data",
     *                   type="array",
     *                   @OA\Items(
     *                      type="object",
     *                      anyOf={
     *                          @OA\Schema(ref="#/components/schemas/anime"),
     *                          @OA\Schema(ref="#/components/schemas/manga"),
     *                          @OA\Schema(ref="#/components/schemas/character"),
     *                          @OA\Schema(ref="#/components/schemas/person"),
     *                      }
     *                  ),
     *              ),
     *          ),
     *     }
     *  ),
     */

    /**
     *  @OA\Get(
     *     path="/random/anime",
     *     operationId="getRandomAnime",
     *     tags={"random"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns Random Anime",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
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

    /**
     *  @OA\Get(
     *     path="/random/manga",
     *     operationId="getRandomManga",
     *     tags={"random"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns Random Manga",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
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

    /**
     *  @OA\Get(
     *     path="/random/characters",
     *     operationId="getRandomCharacters",
     *     tags={"random"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns Random Character",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function characters(Request $request)
    {

        $results = Character::query()
            ->raw(fn($collection) => $collection->aggregate([ ['$sample' => ['size' => 1]] ]));

        return new CharacterCollection(
            $results
        );
    }

    /**
     *  @OA\Get(
     *     path="/random/people",
     *     operationId="getRandomPeople",
     *     tags={"random"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns Random People",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function people(Request $request)
    {

        $results = Person::query()
            ->raw(fn($collection) => $collection->aggregate([ ['$sample' => ['size' => 1]] ]));

        return new PersonCollection(
            $results
        );
    }

    /**
     *  @OA\Get(
     *     path="/random/users",
     *     operationId="getRandomUsers",
     *     tags={"random"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns Random Users",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function users(Request $request)
    {

        $results = Profile::query()
            ->raw(fn($collection) => $collection->aggregate([ ['$sample' => ['size' => 1]] ]));

        return new UserCollection(
            $results
        );
    }
}
