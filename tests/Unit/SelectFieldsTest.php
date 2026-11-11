<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use App\Http\Middleware\SelectFields;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class SelectFieldsTest extends TestCase
{
    public function test_it_projects_a_detail_response_to_requested_top_level_fields(): void
    {
        $response = $this->handle(
            $this->request('/v4/anime/1?fields=mal_id,title', 'v4.anime.main'),
            ['data' => ['mal_id' => 1, 'title' => 'Cowboy Bebop', 'url' => 'https://example.test']]
        );

        $this->assertSame(['mal_id' => 1, 'title' => 'Cowboy Bebop'], $response->getData(true)['data']);
    }

    public function test_it_projects_each_item_in_a_search_collection(): void
    {
        $response = $this->handle(
            $this->request('/v4/anime?fields=mal_id', 'v4.search.anime'),
            ['data' => [
                ['mal_id' => 1, 'title' => 'A', 'url' => 'u1'],
                ['mal_id' => 2, 'title' => 'B', 'url' => 'u2'],
            ]]
        );

        $this->assertSame([['mal_id' => 1], ['mal_id' => 2]], $response->getData(true)['data']);
    }

    public function test_it_returns_the_response_untouched_without_the_parameter(): void
    {
        $payload = ['data' => ['mal_id' => 1, 'title' => 'Cowboy Bebop']];

        $this->assertSame($payload, $this->handle(
            $this->request('/v4/anime/1', 'v4.anime.main'),
            $payload
        )->getData(true));
    }

    public function test_it_rejects_unknown_fields(): void
    {
        $this->expectException(ValidationException::class);

        $this->handle(
            $this->request('/v4/anime/1?fields=mal_id,nope', 'v4.anime.main'),
            ['data' => ['mal_id' => 1]]
        );
    }

    private function request(string $uri, string $routeName): Request
    {
        $request = Request::create($uri);
        $route = new Route(['GET'], parse_url($uri, PHP_URL_PATH), []);
        $route->name($routeName);
        $request->setRouteResolver(fn () => $route);

        return $request;
    }

    private function handle(Request $request, array $payload): JsonResponse
    {
        return (new SelectFields)->handle(
            $request,
            fn (): JsonResponse => new JsonResponse($payload)
        );
    }
}
