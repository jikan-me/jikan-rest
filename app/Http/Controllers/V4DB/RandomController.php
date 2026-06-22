<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\QueryRandomAnimeCommand;
use App\Dto\QueryRandomAnimeListCommand;
use App\Dto\QueryRandomCharacterCommand;
use App\Dto\QueryRandomCharacterListCommand;
use App\Dto\QueryRandomMangaCommand;
use App\Dto\QueryRandomPersonCommand;
use App\Dto\QueryRandomPersonListCommand;
use App\Dto\QueryRandomUserCommand;
use App\Dto\QueryRandomUserListCommand;


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
     *     @OA\Parameter(ref="#/components/parameters/sfw"),
     *     @OA\Parameter(ref="#/components/parameters/unapproved"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns a single random anime resource",
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
     *     path="/random/list/anime",
     *     operationId="getRandomAnimeList",
     *     tags={"random"},
     *
     *     @OA\Parameter(ref="#/components/parameters/sfw"),
     *     @OA\Parameter(ref="#/components/parameters/unapproved"),
     *     @OA\Parameter(ref="#/components/parameters/limit"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns multiple anime resources. You can use `limit` to control the number of items returned. By default it returns 1 and maximum 5",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                   property="data",
     *                   type="array",
     *                   @OA\Items(
     *                      type="object",
     *                      ref="#/components/schemas/anime"
     *                  ),
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function animeList(QueryRandomAnimeListCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/random/manga",
     *     operationId="getRandomManga",
     *     tags={"random"},
     *
     *     @OA\Parameter(ref="#/components/parameters/sfw"),
     *     @OA\Parameter(ref="#/components/parameters/unapproved"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns a single random manga resource or multiple resources in an array when `limit` is supplied",
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
     *     path="/random/list/manga",
     *     operationId="getRandomMangaList",
     *     tags={"random"},
     *
     *     @OA\Parameter(ref="#/components/parameters/sfw"),
     *     @OA\Parameter(ref="#/components/parameters/unapproved"),
     *     @OA\Parameter(ref="#/components/parameters/limit"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns multiple manga resources. You can use `limit` to control the number of items returned. By default it returns 1 and maximum 5",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                   property="data",
     *                   type="array",
     *                   @OA\Items(
     *                      type="object",
     *                      ref="#/components/schemas/manga"
     *                  ),
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function mangaList(QueryRandomAnimeListCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/random/characters",
     *     operationId="getRandomCharacters",
     *     tags={"random"},
     *
     *     @OA\Parameter(ref="#/components/parameters/sfw"),
     *     @OA\Parameter(ref="#/components/parameters/unapproved"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns a single random character resource or multiple resources in an array when `limit` is supplied",
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
     *     path="/random/list/characters",
     *     operationId="getRandomCharactersList",
     *     tags={"random"},
     *
     *     @OA\Parameter(ref="#/components/parameters/sfw"),
     *     @OA\Parameter(ref="#/components/parameters/unapproved"),
     *     @OA\Parameter(ref="#/components/parameters/limit"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns multiple character resources. You can use `limit` to control the number of items returned. By default it returns 1 and maximum 5",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                   property="data",
     *                   type="array",
     *                   @OA\Items(
     *                      type="object",
     *                      ref="#/components/schemas/character"
     *                  ),
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function charactersList(QueryRandomCharacterListCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/random/people",
     *     operationId="getRandomPeople",
     *     tags={"random"},
     *
     *     @OA\Parameter(ref="#/components/parameters/sfw"),
     *     @OA\Parameter(ref="#/components/parameters/unapproved"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns a single random person resource or multiple resources in an array when `limit` is supplied",
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
     *     path="/random/list/people",
     *     operationId="getRandomPeopleList",
     *     tags={"random"},
     *
     *     @OA\Parameter(ref="#/components/parameters/sfw"),
     *     @OA\Parameter(ref="#/components/parameters/unapproved"),
     *     @OA\Parameter(ref="#/components/parameters/limit"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns multiple people resources. You can use `limit` to control the number of items returned. By default it returns 1 and maximum 5",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                   property="data",
     *                   type="array",
     *                   @OA\Items(
     *                      type="object",
     *                      ref="#/components/schemas/person"
     *                  ),
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function peopleList(QueryRandomPersonListCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/random/users",
     *     operationId="getRandomUsers",
     *     tags={"random"},
     *
     *     @OA\Parameter(ref="#/components/parameters/sfw"),
     *     @OA\Parameter(ref="#/components/parameters/unapproved"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns a single random user profile resource or multiple resources in an array when `limit` is supplied",
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

    /**
     *  @OA\Get(
     *     path="/random/list/users",
     *     operationId="getRandomUsersList",
     *     tags={"random"},
     *
     *     @OA\Parameter(ref="#/components/parameters/sfw"),
     *     @OA\Parameter(ref="#/components/parameters/unapproved"),
     *     @OA\Parameter(ref="#/components/parameters/limit"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns multiple user resources. You can use `limit` to control the number of items returned. By default it returns 1 and maximum 5",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                   property="data",
     *                   type="array",
     *                   @OA\Items(
     *                      type="object",
     *                      ref="#/components/schemas/user_profile"
     *                  ),
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function usersList(QueryRandomUserListCommand $command)
    {
        return $this->mediator->send($command);
    }
}
