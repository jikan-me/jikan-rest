<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\ProducerExternalLookupCommand;
use App\Dto\ProducerFullLookupCommand;
use App\Dto\ProducerLookupCommand;
use Illuminate\Http\Request;

class ProducerController extends Controller
{
    /**
     *  @OA\Get(
     *     path="/producers/{id}",
     *     operationId="getProducerById",
     *     tags={"producers"},
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
     *         description="Returns producer resource",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/producer"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function main(ProducerLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/producers/{id}/full",
     *     operationId="getProducerFullById",
     *     tags={"producers"},
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
     *         description="Returns producer resource",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/producer_full"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function full(ProducerFullLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/producers/{id}/external",
     *     operationId="getProducerExternal",
     *     tags={"producers"},
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
     *         description="Returns producer's external links",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/external_links"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function external(ProducerExternalLookupCommand $command)
    {
        return $this->mediator->send($command);
    }
}
