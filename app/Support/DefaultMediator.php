<?php

namespace App\Support;

use App\Contracts\DataRequest;
use App\Contracts\Mediator;
use App\Contracts\RequestHandler;

final class DefaultMediator implements Mediator
{
    /**
     * Handlers resolved lazily on first use, per mediator instance.
     *
     * @var array<string, RequestHandler>
     */
    private array $resolvedHandlers = [];

    /**
     * @param  array<string, \Closure(): RequestHandler>  $handlerFactories
     *                                                                       keyed by the request (DTO) class name.
     */
    public function __construct(private readonly array $handlerFactories) {}

    /**
     * {@inheritDoc}
     */
    public function send(DataRequest $requestData)
    {
        $className = get_class($requestData);

        if (!isset($this->handlerFactories[$className])) {
            return response()->json([
                'message' => 'Programmer error: Request handler not found for request.',
            ], 500);
        }

        $handler = $this->resolvedHandlers[$className] ??= ($this->handlerFactories[$className])();

        return $handler->handle($requestData);
    }
}
