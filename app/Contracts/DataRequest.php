<?php

namespace App\Contracts;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

/**
 * Marker interface to represent a request with a response
 * @template T of ResourceCollection|JsonResource|Response
 */
interface DataRequest
{
}
