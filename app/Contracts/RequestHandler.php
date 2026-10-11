<?php

namespace App\Contracts;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

/**
 * @template TRequest of DataRequest
 * @template TResponse of ResourceCollection|JsonResource|JsonResponse|Response
 */
interface RequestHandler
{
    /**
     * @param TRequest $request
     * @return TResponse
     */
    public function handle($request);

    /**
     * @return class-string<TRequest>
     */
    public function requestClass(): string;
}
