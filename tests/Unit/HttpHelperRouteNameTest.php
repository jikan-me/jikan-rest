<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use App\Http\Controllers\V4DB\AnimeController;
use App\Support\HttpHelper;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Tests\TestCase;

final class HttpHelperRouteNameTest extends TestCase
{
    public function test_named_controller_route_resolves_to_its_route_name(): void
    {
        $route = $this->app['router']->get('/x', [AnimeController::class, 'main'])->name('v4.anime.main');

        $this->assertSame('v4.anime.main', HttpHelper::getRouteName($this->requestFor($route)));
    }

    public function test_closure_route_is_named_closure(): void
    {
        $route = new Route('GET', '/x', fn () => 'ok');

        $this->assertSame('Closure', HttpHelper::getRouteName($this->requestFor($route)));
    }

    public function test_unnamed_controller_route_is_undefined(): void
    {
        $route = $this->app['router']->get('/y', [AnimeController::class, 'full']);

        $this->assertStringEndsWith('Undefined', HttpHelper::getRouteName($this->requestFor($route)));
    }

    public function test_request_without_a_matched_route_is_undefined(): void
    {
        $this->assertStringEndsWith('Undefined', HttpHelper::getRouteName(Request::create('/x')));
    }

    private function requestFor(Route $route): Request
    {
        $request = Request::create('/x');
        $request->setRouteResolver(fn () => $route);

        return $request;
    }
}
