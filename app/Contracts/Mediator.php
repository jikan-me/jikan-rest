<?php

namespace App\Contracts;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Spatie\LaravelData\Data;

interface Mediator
{
    /**
     * Send a request to a single handler
     * @template T
     * @param DataRequest<T> $requestData
     * @return T
     */
    public function send(DataRequest $requestData);
}
