<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use App\Http\Middleware\ConditionalCache;
use App\Support\JikanConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

final class ConditionalCacheTest extends TestCase
{
    public function test_it_adds_a_weak_etag_and_answers_matching_conditionals_with_304(): void
    {
        $middleware = new ConditionalCache(app(JikanConfig::class));
        $first = $middleware->handle(
            $this->request('/v4/anime/1', 'v4.anime.main'),
            fn (): JsonResponse => new JsonResponse(['data' => ['mal_id' => 1]])
        );

        $etag = $first->headers->get('ETag');
        $this->assertNotNull($etag);
        $this->assertStringStartsWith('W/"', $etag);

        $second = $middleware->handle(
            $this->request('/v4/anime/1', 'v4.anime.main', ['If-None-Match' => $etag]),
            fn (): JsonResponse => new JsonResponse(['data' => ['mal_id' => 1]])
        );

        $this->assertSame(Response::HTTP_NOT_MODIFIED, $second->getStatusCode());
    }

    public function test_it_does_not_add_etags_to_non_get_or_error_responses(): void
    {
        $middleware = new ConditionalCache(app(JikanConfig::class));

        $post = $middleware->handle(
            $this->request('/v4/anime/1', 'v4.anime.main', [], 'POST'),
            fn (): JsonResponse => new JsonResponse(['data' => []])
        );
        $this->assertNull($post->headers->get('ETag'));

        $error = $middleware->handle(
            $this->request('/v4/anime/999', 'v4.anime.main'),
            fn (): JsonResponse => new JsonResponse(['status' => 404], 404)
        );
        $this->assertNull($error->headers->get('ETag'));
    }

    private function request(string $uri, string $routeName, array $headers = [], string $method = 'GET'): Request
    {
        $request = Request::create($uri, $method);
        $route = new Route([$method], parse_url($uri, PHP_URL_PATH), []);
        $route->name($routeName);
        $request->setRouteResolver(fn () => $route);

        foreach ($headers as $name => $value) {
            $request->headers->set($name, $value);
        }

        return $request;
    }
}
