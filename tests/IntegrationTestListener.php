<?php /** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests;
use Illuminate\Console\OutputStyle;
use Illuminate\Container\Container;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\Facade;
use \Throwable;
use PHPUnit\Framework\TestListener;

// fixme: with phpunit 10, this should be replaced with the new event system
class IntegrationTestListener implements TestListener
{
    private \Laravel\Lumen\Application $app;

    public function __construct()
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $database = env('DB_DATABASE', 'jikan_tests');
        $app['config']->set('database.connections.mongodb.database', $database === 'jikan' ? 'jikan_tests' : $database);
        $app['config']->set('jikan.micro_caching_enabled', false);
        $this->app = $app;
    }

    public function addError(\PHPUnit\Framework\Test $test, Throwable $t, float $time): void
    {
    }

    public function addWarning(\PHPUnit\Framework\Test $test, \PHPUnit\Framework\Warning $e, float $time): void
    {
    }

    public function addFailure(\PHPUnit\Framework\Test $test, \PHPUnit\Framework\AssertionFailedError $e, float $time): void
    {
    }

    public function addIncompleteTest(\PHPUnit\Framework\Test $test, Throwable $t, float $time): void
    {
    }

    public function addRiskyTest(\PHPUnit\Framework\Test $test, Throwable $t, float $time): void
    {
    }

    public function addSkippedTest(\PHPUnit\Framework\Test $test, Throwable $t, float $time): void
    {
    }

    private function isIntegrationTest(\PHPUnit\Framework\TestSuite $suite): bool
    {
        $suiteName = $suite->getName();
        return in_array($suiteName, [
            "integration", "Tests\Integration", "Integration"
        ]);
    }

    public function startTestSuite(\PHPUnit\Framework\TestSuite $suite): void
    {
        if ($this->isIntegrationTest($suite)) {
            $app = $this->app;
            Container::setInstance($app);
            Facade::setFacadeApplication($app);
            $kernel = $app->make(
                'Illuminate\Contracts\Console\Kernel'
            );
            try {
                $kernel->call('migrate:fresh', []);
            } catch (\Exception $ex) {
                dd($ex);
            }
        }
    }

    public function endTestSuite(\PHPUnit\Framework\TestSuite $suite): void
    {
        if ($this->isIntegrationTest($suite)) {

            $app = $this->app;
            Container::setInstance($app);
            Facade::setFacadeApplication($app);
            $kernel = $app->make(
                'Illuminate\Contracts\Console\Kernel'
            );
            try {
                $kernel->call('migrate:rollback');
            } catch (\Exception $ex) {
                print_r($ex->getMessage());
                print_r($ex);
                throw $ex;
            }
        }
    }

    public function startTest(\PHPUnit\Framework\Test $test): void
    {
    }

    public function endTest(\PHPUnit\Framework\Test $test, float $time): void
    {
    }
}
