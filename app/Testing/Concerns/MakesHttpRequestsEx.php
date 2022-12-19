<?php
namespace App\Testing\Concerns;

trait MakesHttpRequestsEx
{
    protected function getBaseUri(): string
    {
        return "/";
    }

    /**
     * Visit the given URI with a JSON GET request.
     * @param string $uri
     * @param array $headers
     * @return self
     */
    public function getJson(string $uri, array $headers = []): self
    {
        return $this->json('GET', $uri, [], $headers);
    }

    /**
     * @param array $params
     * @return array
     */
    public function getJsonResponse(array $params, ?string $baseUri = null): array
    {
        $parameters = http_build_query($params);
        $uri = $baseUri ?? $this->getBaseUri() . "?" . $parameters;
        $this->getJson($uri);
        return $this->response->json();
    }
}
