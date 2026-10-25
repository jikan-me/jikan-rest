<?php

namespace Tests\Integration;

use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Offline smoke crawl of every named GET route in routes/web.v4.php.
 *
 * MAL is mocked as a 404-responding upstream (see
 * TestCase::mockJikanParserWith404RespondingUpstream) and the database is empty,
 * so no route may answer with a 5xx. The point is to execute every controller,
 * DTO, handler and resource under APP_ENV=testing with Model::shouldBeStrict on,
 * which is how the Producers $fillable and user-list DTO bugs were found
 * (500 instead of 404/400). It deliberately asserts "no 5xx", not payloads;
 * payload shapes are covered by the per-controller tests.
 */
final class RouteSmokeCrawlTest extends TestCase
{
    use ScoutFlush;
    use SyntheticMongoDbTransaction;

    /** Routes that are not part of the data API (docs UI, redirects, static manifests). */
    private const SKIPPED = [
        'v4.root' => 'static index closure, no controller/DTO/model code',
        'l5-swagger.default.api' => 'Swagger UI HTML, depends on generated docs',
        'l5-swagger.default.oauth2_callback' => 'Swagger UI helper',
        'l5-swagger.default.docs' => 'serves the generated OpenAPI file, which is not built in CI',
        'l5-swagger.default.asset' => 'Swagger UI static assets',
    ];

    /** Fixture values for route parameters. */
    private const PARAMS = [
        'id' => 1,
        'episodeId' => 1,
        'username' => 'Xinil',
        'year' => 2024,
        'season' => 'winter',
    ];

    /** Extra query strings exercising DTO branches the bare URL does not reach. */
    private const EXTRA_URLS = [
        '/v4/users/Xinil/animelist?aired_from=1990-01-01&aired_to=2030-01-01',
        '/v4/users/Xinil/mangalist?published_from=1990-01-01&published_to=2030-01-01',
        '/v4/users/Xinil/animelist/watching',
        '/v4/users/Xinil/mangalist/reading',
        '/v4/producers?q=studio&order_by=favorites&sort=desc',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockJikanParserWith404RespondingUpstream();
    }

    /**
     * @return list<string>
     */
    private function crawlableUris(): array
    {
        $uris = [];
        foreach (Route::getRoutes()->getRoutes() as $route) {
            /** @var LaravelRoute $route */
            $name = $route->getName();
            if ($name === null || !in_array('GET', $route->methods(), true)
                || !str_starts_with($route->uri(), 'v4')
                || array_key_exists($name, self::SKIPPED)) {
                continue;
            }

            $uris[$name] = $this->fill($route);
        }
        ksort($uris);

        return $uris;
    }

    private function fill(LaravelRoute $route): string
    {
        // Optional segments ({status?}) are dropped; the bare route is the common case.
        $uri = preg_replace('#/\{[^}]+\?\}#', '', '/'.$route->uri());

        return preg_replace_callback('#\{([^}]+)\}#', function (array $m) use ($route) {
            $this->assertArrayHasKey($m[1], self::PARAMS, "Route {$route->getName()} has an unmapped parameter {$m[1]}; add a fixture to PARAMS.");

            return (string) self::PARAMS[$m[1]];
        }, $uri);
    }

    public function testStrictModelsAreOnUnderTheTestEnvironment(): void
    {
        $this->assertSame('testing', app()->environment());
        $this->assertTrue(Model::preventsLazyLoading());
        $this->assertTrue(Model::preventsSilentlyDiscardingAttributes());
        $this->assertTrue(Model::preventsAccessingMissingAttributes());
    }

    public function testCrawlCoversTheWholeRouteTable(): void
    {
        // Guards against the crawl silently shrinking if route naming changes.
        $this->assertGreaterThanOrEqual(90, count($this->crawlableUris()));
    }

    public function testNoNamedGetRouteReturnsAServerError(): void
    {
        $failures = [];
        foreach ([...$this->crawlableUris(), ...array_combine(self::EXTRA_URLS, self::EXTRA_URLS)] as $name => $uri) {
            $response = $this->get($uri);
            if ($response->getStatusCode() >= 500) {
                $cause = $response->exception ? get_class($response->exception).': '.$response->exception->getMessage() : $response->getContent();
                $failures[] = $response->getStatusCode()."  $uri  ($name)  ".substr((string) $cause, 0, 300);
            }
        }

        $this->assertSame([], $failures, "Routes returning 5xx:\n".implode("\n", $failures));
    }
}
