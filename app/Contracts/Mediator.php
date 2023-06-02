<?php

namespace App\Contracts;

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
