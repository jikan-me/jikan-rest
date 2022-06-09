<?php

namespace App\Http\Controllers\V4DB;

use App\Http\Resources\V4\ProducerCollection;
use Illuminate\Http\Request;

class ProducerController extends ControllerWithQueryBuilderProvider
{
    /**
     *  @OA\Get(
     *     path="/producers",
     *     operationId="getProducers",
     *     tags={"producers"},
     *
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns producers collection",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/producers"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function main(Request $request)
    {
        return $this->preparePaginatedResponse(ProducerCollection::class, "producer", $request);
    }
}
