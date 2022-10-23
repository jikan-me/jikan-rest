<?php

namespace App\Listeners;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use pushrbx\LumenRoadRunner\Events\LoopErrorOccurredEvent;

class PsrWorkerErrorListener
{
    private \App\Exceptions\Handler $exceptionHandler;
    private Logger $logger;

    public function __construct()
    {
        $this->exceptionHandler = new \App\Exceptions\Handler;
        $this->logger = new Logger('psr-worker');
        $this->logger->pushHandler(new StreamHandler(storage_path().'/logs/psr-worker.log'), env('APP_DEBUG') ? Logger::DEBUG : Logger::WARNING);
    }

    public function handle(LoopErrorOccurredEvent $event): void
    {
        try {
            $this->exceptionHandler->report($event->exception());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
