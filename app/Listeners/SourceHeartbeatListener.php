<?php

namespace App\Listeners;

use App\Events\ExampleEvent;
use App\Events\SourceHeartbeatEvent;
use App\Providers\SourceHeartbeatProvider;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FileNotFoundException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class SourceHeartbeatListener
{

    private $logger;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->logger = new Logger('source-health-monitor');
        $this->logger->pushHandler(new StreamHandler(storage_path().'/logs/source-health-monitor.log'), env('APP_DEBUG') ? Logger::DEBUG : Logger::WARNING);

        if (SourceHeartbeatProvider::isFailoverEnabled()) {
            $lastFailoverLockTimestamp = $this->getLastFailoverLockTimestamp();
            $this->logger->debug('Failover is RUNNING');

            // Disable failover if it has expired
            if (time() > ($lastFailoverLockTimestamp + env('SOURCE_BAD_HEALTH_RECHECK'))) {
                // Disable failover if successful requests score
                $this->attemptDisableFailover();
            }
        }
    }

    /**
     * Handle the event.
     *
     * @param  ExampleEvent  $event
     * @return void
     */
    public function handle(SourceHeartbeatEvent $event)
    {
        $eventCount = $this->insertFail($event);
        $this->logger->debug('Event count: '.$eventCount);

        if ($this->getSuccessfulRequestsScore() <= 0.25) {
            $this->enableFailover();
        }
    }

    private function insertFail(SourceHeartbeatEvent $event) : int
    {
        $fails = $this->getRecentFails();
        $fails[] = [time(), $event->status, $event->health];

        $failsJson = json_encode($fails);
        Storage::put('failovers.json', $failsJson);

        return count($fails);
    }

    private function enableFailover()
    {
        // create lock file
        Storage::put('source_failover.lock', '');
        $this->logger->debug('Failover ENABLED');
    }

    private function disableFailover()
    {
        // delete lock file
        Storage::delete('source_failover.lock');

        // Delete meta
        Storage::delete('failovers.json');
    }

    private function attemptDisableFailover()
    {
        $score = $this->getSuccessfulRequestsScore();

        if ($score >= env('SOURCE_GOOD_HEALTH_SCORE', 0.9)) {
            $this->disableFailover();
            $this->logger->debug('Failover disabled; Score: '.$score);
            $this->logger->debug('Failover DISABLED');
            return true;
        }

        return false;
    }

    private function getLastFailoverLockTimestamp()
    {
        try {
            return Storage::lastModified('source_failover.lock');
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getRecentFails()
    {
        try {
            $failsJson = Storage::get('failovers.json');
            $fails = json_decode($failsJson, true);
        } catch (\Exception $e) {
        }

        $fails = $fails ?? [];

        // remove any fails greater than SOURCE_BAD_HEALTH_RANGE
        foreach ($fails as $fail) {

            if (!isset($fail[0])) {
                unset($fail);
                continue;
            }

            if ($fail[0] >= (time()-env('SOURCE_BAD_HEALTH_RANGE'))) {
                unset($fail);
            }
        }

        // slice
        if (count($fails) > env('SOURCE_BAD_HEALTH_MAX_STORE')) {
            $fails = array_slice($fails, 0 - env('SOURCE_BAD_HEALTH_MAX_STORE'));
        }

        return $fails;
    }

    private function getSuccessfulRequestsScore() : float
    {
        $fails = $this->getRecentFails();
        $score = 0;
        $totalFails = count($fails) - 1;

        foreach ($fails as $fail) {
            if ((int) $fail[2] === SourceHeartbeatEvent::GOOD_HEALTH) {
                $score++;
            }
        }

        $scored = $score / max($totalFails, 1);
        $this->logger->debug('Failover successful requests score: '.$scored);

        return $scored;
    }
}
