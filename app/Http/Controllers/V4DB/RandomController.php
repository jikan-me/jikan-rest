<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\QueryRandomAnimeCommand;
use App\Dto\QueryRandomCharacterCommand;
use App\Dto\QueryRandomMangaCommand;
use App\Dto\QueryRandomPersonCommand;
use App\Dto\QueryRandomUserCommand;


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
     *     @OA\Parameter(ref="#/components/parameters/limit"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns a single random anime resource or multiple resources in an array when `limit` is supplied",
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
     *     @OA\Parameter(ref="#/components/parameters/limit"),
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
     *     path="/random/characters",
     *     operationId="getRandomCharacters",
     *     tags={"random"},
     *
     *     @OA\Parameter(ref="#/components/parameters/limit"),
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
     *     path="/random/people",
     *     operationId="getRandomPeople",
     *     tags={"random"},
     *
     *     @OA\Parameter(ref="#/components/parameters/limit"),
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
     *     path="/random/users",
     *     operationId="getRandomUsers",
     *     tags={"random"},
     *
     *     @OA\Parameter(ref="#/components/parameters/limit"),
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
}
