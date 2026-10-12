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
    public function test_class_based_route_resolves_to_controller_short_name_and_method(): void
    {
        $route = new Route('GET', '/x', [AnimeController::class, 'main']);

        $this->assertSame('AnimeController@main', HttpHelper::getRouteName($this->requestFor($route)));
    }

    public function test_legacy_string_action_resolves_the_same_way(): void
    {
        // registered through the router, which normalises `uses` into a controller action
        $route = $this->app['router']->get('/x', ['uses' => AnimeController::class.'@full']);

        $this->assertSame('AnimeController@full', HttpHelper::getRouteName($this->requestFor($route)));
    }

    public function test_closure_route_is_named_closure(): void
    {
        $route = new Route('GET', '/x', fn () => 'ok');

        $this->assertSame('Closure', HttpHelper::getRouteName($this->requestFor($route)));
    }

    public function test_request_without_a_matched_route_is_undefined(): void
    {
        $this->assertSame('App\Undefined', HttpHelper::getRouteName(Request::create('/x')));
    }

    private function requestFor(Route $route): Request
    {
        $request = Request::create('/x');
        $request->setRouteResolver(fn () => $route);

        return $request;
    }
}
