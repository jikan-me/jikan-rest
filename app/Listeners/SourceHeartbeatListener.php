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

    private Logger $logger;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->logger = new Logger('source-health-monitor');
        $this->logger->pushHandler(new StreamHandler(storage_path().'/logs/source-health-monitor.log'), config('app.debug') ? Logger::DEBUG : Logger::WARNING);

        if (SourceHeartbeatProvider::isFailoverEnabled()) {
            $lastFailoverLockTimestamp = $this->getLastFailoverLockTimestamp();

            if (config('app.debug')) {
                $this->logger->debug('Failover is RUNNING');
            }

            // Disable failover if it has expired
            if (time() > ($lastFailoverLockTimestamp + config('jikan.source_bad_health_recheck'))) {
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

        if (config('app.debug')) {
            $this->logger->debug('Event count: '.$eventCount);
        }

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
        Storage::put('source_failover_last_downtime', time());

        if (config('app.debug')) {
            $this->logger->debug('Failover ENABLED');
        }
    }

    private function disableFailover()
    {
        // delete lock file
        Storage::delete('source_failover.lock');

        // Delete meta
        Storage::delete('failovers.json');
    }

    private function attemptDisableFailover(): bool
    {
        $score = $this->getSuccessfulRequestsScore();

        if ($score >= config('jikan.source_good_health_score')) {
            $this->disableFailover();
            if (config('app.debug')) {
                $this->logger->debug('Failover disabled; Score: '.$score);
                $this->logger->debug('Failover DISABLED');
            }

            return true;
        }

        return false;
    }

    private function getLastFailoverLockTimestamp(): int
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

        return SourceHeartbeatProvider::pruneFails($fails ?? []);
    }

    private function getSuccessfulRequestsScore() : float
    {
        $fails = $this->getRecentFails();
        $score = 0;
        $totalFails = count($fails);

        foreach ($fails as $fail) {
            if ((int) $fail[2] === SourceHeartbeatEvent::GOOD_HEALTH) {
                $score++;
            }
        }

        $scored = $score / max($totalFails, 1);

        if(config('app.debug')) {
            $this->logger->debug('Failover successful requests score: '.$scored);
        }

        return $scored;
    }
}
