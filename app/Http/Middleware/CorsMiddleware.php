<?php

namespace App\Http\Middleware;

use Fruitcake\Cors\CorsService;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Middleware\HandleCors;
use Laravel\Lumen\Http\ResponseFactory;

class CorsMiddleware extends HandleCors
{
    public function __construct(Container $container, CorsService $cors, private readonly ResponseFactory $responseFactory)
    {
        parent::__construct($container, $cors);
    }

    public function handle($request, \Closure $next): Response | JsonResponse | RedirectResponse
    {
        if (! $this->hasMatchingPath($request)) {
            return $next($request);
        }

        $this->cors->setOptions($this->container['config']->get('cors', []));

        if ($this->cors->isPreflightRequest($request)) {
            $symfonyResponse = $this->cors->handlePreflightRequest($request);

            $this->cors->varyHeader($symfonyResponse, 'Access-Control-Request-Method');
            $lumenResponse = $this->responseFactory->make($symfonyResponse->getContent(), $symfonyResponse->getStatusCode(), $symfonyResponse->headers->all());
            $lumenResponse->setProtocolVersion("1.1");

            return $lumenResponse;
        }

        $response = $next($request);

        if ($request->getMethod() === 'OPTIONS') {
            $this->cors->varyHeader($response, 'Access-Control-Request-Method');
        }

        $symfonyResponse = $this->cors->addActualRequestHeaders($response, $request);
        $lumenResponse = $this->responseFactory->make($symfonyResponse->getContent(), $symfonyResponse->getStatusCode(), $symfonyResponse->headers->all());
        $lumenResponse->setProtocolVersion("1.1");

        return $lumenResponse;
    }
}
