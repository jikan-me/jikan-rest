<?php

namespace App\Http\QueryBuilder\Scraper;

use App\Providers\SerializerFactory;

/**
 *
 */
class ScraperHandler
{
    /**
     * @var mixed
     */
    private mixed $response;

    /**
     * @param string $getterFuncName
     * @param string $requestResolverClassName
     * @param QueryResolver $queryResolver
     */
    public function __construct(string $getterFuncName, string $requestResolverClassName, QueryResolver $queryResolver)
    {
        $getterFuncName = "get".ucfirst(strtolower($getterFuncName));

        $this->response = app('JikanParser')
            ->$getterFuncName(
                new $requestResolverClassName(
                    ...$queryResolver->getQueryValuesAsArray()
                )
            );
    }

    /**
     * @return string
     */
    public function getSerializedJSON(): array
    {
        return \json_decode(
            SerializerFactory::createV4()
                ->serialize($this->response, 'json'),
            true
        );
    }
}
