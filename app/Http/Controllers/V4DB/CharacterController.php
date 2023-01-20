<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Character;
use App\Dto\CharacterAnimeLookupCommand;
use App\Dto\CharacterFullLookupCommand;
use App\Dto\CharacterLookupCommand;
use App\Dto\CharacterMangaLookupCommand;
use App\Dto\CharacterPicturesLookupCommand;
use App\Dto\CharacterVoicesLookupCommand;
use App\Http\HttpHelper;
use App\Http\HttpResponse;
use App\Http\Resources\V4\CharacterAnimeCollection;
use App\Http\Resources\V4\CharacterMangaCollection;
use App\Http\Resources\V4\CharacterMangaResource;
use App\Http\Resources\V4\CharacterSeiyuuCollection;
use App\Http\Resources\V4\PersonMangaCollection;
use App\Http\Resources\V4\PicturesResource;
use App\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\Request\Anime\AnimePicturesRequest;
use Jikan\Request\Character\CharacterRequest;
use Jikan\Request\Character\CharacterPicturesRequest;
use MongoDB\BSON\UTCDateTime;

class CharacterController extends Controller
{
    /**
     *  @OA\Get(
     *     path="/characters/{id}/full",
     *     operationId="getCharacterFullById",
     *     tags={"characters"},
     *
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns complete character resource data",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/character_full"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function full(Request $request, int $id)
    {
        $command = CharacterFullLookupCommand::from($request, $id);
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/characters/{id}",
     *     operationId="getCharacterById",
     *     tags={"characters"},
     *
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns character resource",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/character"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function main(Request $request, int $id)
    {
        $command = CharacterLookupCommand::from($request, $id);
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/characters/{id}/anime",
     *     operationId="getCharacterAnime",
     *     tags={"characters"},
     *
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns anime that character is in",
     *         @OA\JsonContent(ref="#/components/schemas/character_anime")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function anime(Request $request, int $id)
    {
        $command = CharacterAnimeLookupCommand::from($request, $id);
        return $this->mediator->send($command);
    }


    /**
     *  @OA\Get(
     *     path="/characters/{id}/manga",
     *     operationId="getCharacterManga",
     *     tags={"characters"},
     *
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns manga that character is in",
     *         @OA\JsonContent(ref="#/components/schemas/character_manga")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function manga(Request $request, int $id)
    {
        $command = CharacterMangaLookupCommand::from($request, $id);
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/characters/{id}/voices",
     *     operationId="getCharacterVoiceActors",
     *     tags={"characters"},
     *
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns the character's voice actors",
     *         @OA\JsonContent(ref="#/components/schemas/character_voice_actors")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function voices(Request $request, int $id)
    {
        $command = CharacterVoicesLookupCommand::from($request, $id);
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/characters/{id}/pictures",
     *     operationId="getCharacterPictures",
     *     tags={"characters"},
     *
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns pictures related to the entry",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/character_pictures"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     *
     *  @OA\Schema(
     *      schema="character_pictures",
     *      description="Character Pictures",
     *      @OA\Property(
     *          property="data",
     *          type="array",
     *
     *          @OA\Items(
     *              @OA\Property(
     *                  property="image_url",
     *                  type="string",
     *                  description="Default JPG Image Size URL",
     *                  nullable=true
     *              ),
     *              @OA\Property(
     *                  property="large_image_url",
     *                  type="string",
     *                  description="Large JPG Image Size URL",
     *                  nullable=true
     *              ),
     *          )
     *      )
     *  )
     */
    public function pictures(Request $request, int $id)
    {
        $command = CharacterPicturesLookupCommand::from($request, $id);
        return $this->mediator->send($command);
    }
}
