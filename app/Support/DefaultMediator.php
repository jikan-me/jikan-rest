<?php

namespace App\Support;

use App\Contracts\DataRequest;
use App\Contracts\Mediator;
use App\Contracts\RequestHandler;
use Illuminate\Support\Collection;

final class DefaultMediator implements Mediator
{
    /**
     * @var Collection<string, RequestHandler> $requestHandlers
     */
    private readonly Collection $requestHandlers;

    public function __construct(RequestHandler ...$requestHandlers)
    {
        $this->requestHandlers = collect($requestHandlers)->flatMap(fn ($x) => [$x->requestClass() => $x]);
    }

    /**
     * @inheritDoc
     */
    public function send(DataRequest $requestData)
    {
        $className = get_class($requestData);

        if (!$this->requestHandlers->has($className)) {
            return response()->json([
                "message" => "Programmer error: Request handler not found for request."
            ], 500);
        }

        /**
         * @var RequestHandler $handler
         */
        $handler = $this->requestHandlers->get($className);

        return $handler->handle($requestData);
    }
}
