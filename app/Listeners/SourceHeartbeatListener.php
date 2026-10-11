<?php

namespace App\Listeners;

use App\Events\SourceHeartbeatEvent;
use App\Providers\SourceHeartbeatProvider;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Records source-health heartbeats into MongoDB (atomic insertOne per event,
 * previously a non-atomic failovers.json read-modify-write) and manages the
 * failover lock / last-downtime markers, which live in Mongo too.
 */
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
            $lastFailoverLockTimestamp = SourceHeartbeatProvider::getFailoverLockTimestamp();

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
     * @param  SourceHeartbeatEvent  $event
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

    /**
     * Atomically record the heartbeat event; returns the number of events
     * currently recorded within the recency window.
     */
    private function insertFail(SourceHeartbeatEvent $event) : int
    {
        SourceHeartbeatProvider::recordEvent($event->status, $event->health);

        return count(SourceHeartbeatProvider::getRecentEvents());
    }

    private function enableFailover()
    {
        // Atomically upsert the lock + last-downtime markers in Mongo.
        SourceHeartbeatProvider::setFailoverLock(time());
        SourceHeartbeatProvider::setLastDowntime(time());

        if (config('app.debug')) {
            $this->logger->debug('Failover ENABLED');
        }
    }

    private function disableFailover()
    {
        // Remove the lock and the recorded events (the old file scheme dropped
        // both failovers.json and the lock file when failover was disabled).
        SourceHeartbeatProvider::clearFailoverLock();
        SourceHeartbeatProvider::clearEvents();
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

    private function getRecentFails()
    {
        return SourceHeartbeatProvider::getRecentEvents();
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