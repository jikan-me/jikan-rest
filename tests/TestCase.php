<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests;
use App\Exceptions\CustomTestException;
use App\Testing\Concerns\MakesHttpRequestsEx;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use App\Testing\TestExceptionsHandler;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Illuminate\Testing\TestResponse;
use Jikan\MyAnimeList\MalClient;
use Laravel\Lumen\Application;
use Laravel\Lumen\Testing\TestCase as LumenTestCase;
use MongoDB\BSON\UTCDateTime;
use Spatie\Enum\Faker\FakerEnumProvider;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class TestCase extends LumenTestCase
{
    use MakesHttpRequestsEx;
    protected Generator $faker;
    protected int $maxResultsPerPage;

    protected function dummyScraperResultDocument(string $uri, string $mediaType, array $content, ?string $wrapKey = null): array
    {
        return [
            "createdAt" => new UTCDateTime(),
            "modifiedAt" => new UTCDateTime(),
            "request_hash" => "request:$mediaType:" . sha1($uri),
            ...($wrapKey !== null ? [$wrapKey => $content] : $content)
        ];
    }

    protected function dummyResultsDocument(string $uri, string $mediaType, array $resultsContent, $hasNextPage = false, $lastVisiblePage = 1): array
    {
        return $this->dummyScraperResultDocument(
            $uri,
            $mediaType,
            [
                "results" => $resultsContent,
                "has_next_page" => $hasNextPage,
                "last_visible_page" => $lastVisiblePage
            ]
        );
    }

    protected function givenDummyCharactersStaffData($uri, $mediaType)
    {
        DB::table($mediaType === "anime" ? $mediaType."_characters_staff" : $mediaType."_characters")->insert([
            "createdAt" => new UTCDateTime(),
            "modifiedAt" => new UTCDateTime(),
            "request_hash" => "request:$mediaType:" . sha1($uri),
            "characters" => [
                [
                    "character" => [
                        "mal_id" => 3,
                        "url" => "https://myanimelist.net/character/3/Jet_Black",
                        "images" => [
                            "jpg" => [
                                "image_url" => "https://cdn.myanimelist.net/images/characters/11/253723.jpg?s=6c8a19a79a88c46ae15f30e3ef5fd839",
                                "small_image_url" => "https://cdn.myanimelist.net/images/characters/11/253723t.jpg?s=6c8a19a79a88c46ae15f30e3ef5fd839"
                            ],
                            "webp" => [
                                "image_url" => "https://cdn.myanimelist.net/images/characters/11/253723.webp?s=6c8a19a79a88c46ae15f30e3ef5fd839",
                                "small_image_url" => "https://cdn.myanimelist.net/images/characters/11/253723t.webp?s=6c8a19a79a88c46ae15f30e3ef5fd839"
                            ]
                        ],
                        "name" => "Black, Jet"
                    ],
                    "role" => "Main",
                    "favorites" => 1,
                    ...($mediaType === "anime" ? [
                        "voice_actors" => [
                            [
                                "person" => [
                                    "mal_id" => 357,
                                    "url" => "https://myanimelist.net/people/357/Unshou_Ishizuk",
                                    "images" => [
                                        "jpg" => [
                                            "image_url" => "https://cdn.myanimelist.net/images/voiceactors/2/17135.jpg?s=5925123b8a7cf9b51a445c225442f0ef"
                                        ]
                                    ],
                                    "name" => "Ishizuka, Unshou"
                                ],
                                "language" => "Japanese"
                            ]
                        ]
                    ] : [])
                ]
            ],
            ...(
                $mediaType === "anime" ? [
                    "staff" => [
                        [
                            "person" => [
                                "mal_id" => 40009,
                                "url" => "https://myanimelist.net/people/40009/Yutaka_Maseba",
                                "images" => [
                                    "jpg" => [
                                        "image_url" => "https://cdn.myanimelist.net/images/voiceactors/3/40216.jpg?s=d9fb7a625868ec7d9cd3804fa0da3fd6"
                                    ]
                                ],
                                "name" => "Maseba, Yutaka"
                            ],
                            "positions" => [
                                "Producer"
                            ]
                        ]
                    ]
                ] : []
            )
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = FakerFactory::create();
        $this->faker->addProvider(new FakerEnumProvider($this->faker));
        $this->maxResultsPerPage = env("MAX_RESULTS_PER_PAGE", 25);
    }

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        /**
         * @var Application $app
         */
        $app = require __DIR__.'/../bootstrap/app.php';
        // a http client which fails the tests if requests are leaking to MAL.
        $mockHttpClient = \Mockery::mock(HttpClientInterface::class);
        /** @noinspection PhpParamsInspection */
        $mockHttpClient->allows()
            ->request(\Mockery::any(), \Mockery::any(), \Mockery::any())
            ->andThrow(new CustomTestException("Http Request to MAL server was attempted during testing. By default we throw this exception to indicate a buggy test."));
        $jikan = new \Jikan\MyAnimeList\MalClient($mockHttpClient);
        $app->instance('JikanParser', $jikan);
        $app->singleton(ExceptionHandler::class, TestExceptionsHandler::class);
        $database = env('DB_DATABASE', 'jikan_tests');
        $app['config']->set('database.connections.mongodb.database', $database === 'jikan' ? 'jikan_tests' : $database);
        $app['config']->set('jikan.micro_caching_enabled', false);
        $app->register(TestServiceProvider::class);
        Container::setInstance($app);
        /** @noinspection PhpParamsInspection */
        Facade::setFacadeApplication($app);

        return $app;
    }

    protected function setUpTraits()
    {
        parent::setUpTraits();
        $uses = array_flip(class_uses_recursive(get_class($this)));

        // we want to empty the search index
        if (isset($uses[ScoutFlush::class])) {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->runScoutFlush();
        }

        if (isset($uses[SyntheticMongoDbTransaction::class])) {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->beginDatabaseTransaction();
        }
    }

    public function assertPaginationData(int $expectedCount, ?int $expectedTotal = null, ?int $perPage = null): TestResponse
    {
        if (is_null($expectedTotal))
        {
            $expectedTotal = $expectedCount;
        }

        if (is_null($perPage))
        {
            $perPage = $this->maxResultsPerPage;
        }

        $this->response->assertJsonPath("pagination.items.count", $expectedCount);
        $this->response->assertJsonPath("pagination.items.total", $expectedTotal);
        return $this->response->assertJsonPath("pagination.items.per_page", $perPage);
    }

    public function assertCollectionsStrictlyEqual(Collection $expectedItems, Collection $actualItems): void
    {
        $this->assertEquals(0, $expectedItems->diff($actualItems)->count());
        $this->assertEquals($expectedItems->toArray(), $actualItems->toArray());
    }

    protected function mockJikanParserWith404RespondingUpstream()
    {
        $httpClient = \Mockery::mock(HttpClientInterface::class);
        $response = \Mockery::mock(ResponseInterface::class);
        /** @noinspection PhpParamsInspection */
        $httpClient->allows()->request(\Mockery::any(), \Mockery::any(), \Mockery::any())->andReturn($response);
        $response->allows([
            "getStatusCode" => 404,
            "getHeaders" => [],
            "getContent" => ""
        ]);
        $this->app->instance("HttpClient", $httpClient);
        $this->app->instance("JikanParser", new MalClient($httpClient));
    }
}
