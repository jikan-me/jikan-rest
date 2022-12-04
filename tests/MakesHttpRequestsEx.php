<?php

trait MakesHttpRequestsEx
{
    /**
     * Visit the given URI with a JSON GET request.
     * @param string $uri
     * @param array $headers
     * @return TestCase
     */
    public function getJson(string $uri, array $headers = []): TestCase
    {
        return $this->json('GET', $uri, [], $headers);
    }
}
