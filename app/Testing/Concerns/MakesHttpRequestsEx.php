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
     * GET the endpoint with the given query parameters and return the response.
     *
     * When $uri is given it is requested verbatim and $params is NOT appended
     * (this is how the helper has always behaved).
     *
     * @param  array<string, mixed>  $params
     */
    public function getJsonWithParams(array $params, ?string $uri = null): TestResponse
    {
        $uri ??= $this->getBaseUri().'?'.http_build_query($params);

        return $this->getJson($uri);
    }
}
