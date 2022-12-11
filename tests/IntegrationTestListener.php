<?php /** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests;
use \Throwable;
use PHPUnit\Framework\TestListener;

// fixme: with phpunit 10, this should be replaced with the new event system
class IntegrationTestListener implements TestListener
{
    private $app;

    public function __construct()
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $database = env('DB_DATABASE', 'jikan_tests');
        $app['config']->set('database.connections.mongodb.database', $database === 'jikan' ? 'jikan_tests' : $database);
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

    public function startTestSuite(\PHPUnit\Framework\TestSuite $suite): void
    {
        echo $suite->getName();
        if ($suite->getName() == "integration") {
            $app = $this->app;
            $kernel = $app->make(
                'Illuminate\Contracts\Console\Kernel'
            );
            try {
                $kernel->call('migrate:fresh', []);
            } catch (\Exception $ex) {
                print_r($ex);
            }
        }
    }

    public function endTestSuite(\PHPUnit\Framework\TestSuite $suite): void
    {
        if ($suite->getName() == "integration") {
            $app = $this->app;
            $kernel = $app->make(
                'Illuminate\Contracts\Console\Kernel'
            );
            $kernel->call('migrate:rollback');
        }
    }

    public function startTest(\PHPUnit\Framework\Test $test): void
    {
        // TODO: Implement startTest() method.
    }

    public function endTest(\PHPUnit\Framework\Test $test, float $time): void
    {
        // TODO: Implement endTest() method.
    }
}
