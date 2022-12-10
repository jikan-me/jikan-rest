<?php
use App\Testing\Concerns\MakesHttpRequestsEx;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;

abstract class TestCase extends Laravel\Lumen\Testing\TestCase
{
    use MakesHttpRequestsEx;
    protected Faker\Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Faker\Factory::create();
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
}
