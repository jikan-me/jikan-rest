<?php
namespace App\Http\Concerns;

trait MakesHttpRequestsEx
{
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
}
