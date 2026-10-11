<?php

namespace App\Testing\Concerns;

use Illuminate\Testing\TestResponse;

/**
 * @codeCoverageIgnore
 */
trait MakesHttpRequestsEx
{
    protected function getBaseUri(): string
    {
        return '/';
    }

    /**
     * Visit the given URI with a JSON GET request.
     */
    public function getJson($uri, array $headers = [], $options = 0)
    {
        return $this->json('GET', $uri, [], $headers, $options);
    }

    public function getJsonResponse(array $params, ?string $baseUri = null): array
    {
        $parameters = http_build_query($params);
        $uri = $baseUri ?? $this->getBaseUri().'?'.$parameters;

        /** @var TestResponse $response */
        $response = $this->getJson($uri);

        // Lumen exposed the last response via $this->response; Laravel returns it.
        $this->response = $response;

        return $response->json();
    }
}
