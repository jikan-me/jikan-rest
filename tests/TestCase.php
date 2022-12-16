<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests;
use App\Testing\Concerns\MakesHttpRequestsEx;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Testing\TestResponse;
use Laravel\Lumen\Testing\TestCase as LumenTestCase;

abstract class TestCase extends LumenTestCase
{
    use MakesHttpRequestsEx;
    protected Generator $faker;
    protected int $maxResultsPerPage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
        $this->maxResultsPerPage = env("MAX_RESULTS_PER_PAGE", 25);
    }

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $database = env('DB_DATABASE', 'jikan_tests');
        $app['config']->set('database.connections.mongodb.database', $database === 'jikan' ? 'jikan_tests' : $database);

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
}
