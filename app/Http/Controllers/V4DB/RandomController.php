<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Character;
use App\Dto\QueryRandomAnimeCommand;
use App\Dto\QueryRandomCharacterCommand;
use App\Dto\QueryRandomMangaCommand;
use App\Dto\QueryRandomPersonCommand;
use App\Dto\QueryRandomUserCommand;
use App\Http\HttpHelper;
use App\Http\HttpResponse;
use App\Http\Resources\V4\AnimeCollection;
use App\Http\Resources\V4\AnimeResource;
use App\Http\Resources\V4\CharacterCollection;
use App\Http\Resources\V4\CharacterResource;
use App\Http\Resources\V4\CommonResource;
use App\Http\Resources\V4\MangaCollection;
use App\Http\Resources\V4\MangaResource;
use App\Http\Resources\V4\PersonCollection;
use App\Http\Resources\V4\PersonResource;
use App\Http\Resources\V4\ProfileResource;
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
     *         description="Returns a random anime resource",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/anime"
     *              )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function anime(QueryRandomAnimeCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/random/manga",
     *     operationId="getRandomManga",
     *     tags={"random"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns a random manga resource",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/manga"
     *              )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function manga(QueryRandomMangaCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/random/characters",
     *     operationId="getRandomCharacters",
     *     tags={"random"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns a random character resource",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/character"
     *              )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function characters(QueryRandomCharacterCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/random/people",
     *     operationId="getRandomPeople",
     *     tags={"random"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns a random person resource",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/person"
     *              )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function people(QueryRandomPersonCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/random/users",
     *     operationId="getRandomUsers",
     *     tags={"random"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns a random user profile resource",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/user_profile"
     *              )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function users(QueryRandomUserCommand $command)
    {
        return $this->mediator->send($command);
    }
}
