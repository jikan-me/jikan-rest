<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\PersonAnimeLookupCommand;
use App\Dto\PersonFullLookupCommand;
use App\Dto\PersonLookupCommand;
use App\Dto\PersonMangaLookupCommand;
use App\Dto\PersonPicturesLookupCommand;
use App\Dto\PersonVoicesLookupCommand;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    /**
     *  @OA\Get(
     *     path="/people/{id}/full",
     *     operationId="getPersonFullById",
     *     tags={"people"},
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
     *                 ref="#/components/schemas/person_full"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function full(Request $request, int $id)
    {
        $command = PersonFullLookupCommand::from($request, $id);
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/people/{id}",
     *     operationId="getPersonById",
     *     tags={"people"},
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
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/person"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function main(Request $request, int $id)
    {
        $command = PersonLookupCommand::from($request, $id);
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/people/{id}/anime",
     *     operationId="getPersonAnime",
     *     tags={"people"},
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
     *         description="Returns person's anime staff positions",
     *         @OA\JsonContent(ref="#/components/schemas/person_anime")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function anime(Request $request, int $id)
    {
        $command = PersonAnimeLookupCommand::from($request, $id);
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/people/{id}/voices",
     *     operationId="getPersonVoices",
     *     tags={"people"},
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
     *         description="Returns person's voice acting roles",
     *         @OA\JsonContent(ref="#/components/schemas/person_voice_acting_roles")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function voices(Request $request, int $id)
    {
        $command = PersonVoicesLookupCommand::from($request, $id);
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/people/{id}/manga",
     *     operationId="getPersonManga",
     *     tags={"people"},
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
     *         description="Returns person's published manga works",
     *         @OA\JsonContent(ref="#/components/schemas/person_manga")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function manga(Request $request, int $id)
    {
        $command = PersonMangaLookupCommand::from($request, $id);
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/people/{id}/pictures",
     *     operationId="getPersonPictures",
     *     tags={"people"},
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
     *         description="Returns a list of pictures of the person",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/person_pictures"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     *
     *  @OA\Schema(
     *      schema="person_pictures",
     *      description="Character Pictures",
     *      @OA\Property(
     *          property="data",
     *          type="array",
     *
     *          @OA\Items(
     *              type="object",
     *              ref="#/components/schemas/people_images"
     *          )
     *      )
     *  )
     */
    public function pictures(Request $request, int $id)
    {
        $command = PersonPicturesLookupCommand::from($request, $id);
        return $this->mediator->send($command);
    }
}
