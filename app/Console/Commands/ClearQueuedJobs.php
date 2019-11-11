<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ClearQueuedJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all queued jobs';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Stop Supervisor
        $process = Process::fromShellCommandline('service supervisor start');
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();

        // Remove jobs from queue
        $process = Process::fromShellCommandline('redis-cli --scan --pattern queue_update:* | xargs redis-cli del');
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();

        $process = Process::fromShellCommandline('redis-cli --scan --pattern queues:* | xargs redis-cli del');
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();

        // Start Supervisor
        $process = Process::fromShellCommandline('sudo service supervisor stop');
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();
    }
}
