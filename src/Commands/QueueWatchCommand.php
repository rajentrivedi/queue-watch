<?php

namespace QueueWatch\QueueWatch\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class QueueWatchCommand extends Command
{
    protected $signature = 'queue:work:watch
                            {connection? : The name of the queue connection to work}
                            {--queue= : The names of the queues to work}
                            {--daemon : Run the worker in daemon mode (Deprecated)}
                            {--once : Only process the next job on the queue}
                            {--stop-when-empty : Stop when the queue is empty}
                            {--delay=0 : The number of seconds to delay failed jobs (Deprecated)}
                            {--backoff=0 : The number of seconds to wait before retrying a job that encountered an uncaught exception}
                            {--max-jobs=0 : The number of jobs to process before stopping}
                            {--max-time=0 : The maximum number of seconds the worker should run}
                            {--force : Force the worker to run even in maintenance mode}
                            {--memory=128 : The memory limit in megabytes}
                            {--sleep=3 : Number of seconds to sleep when no job is available}
                            {--timeout=60 : The number of seconds a child process can run}
                            {--tries=1 : Number of times to attempt a job before logging it failed}';

    protected $description = 'Start the queue worker with file watching capabilities';

    protected $process;

    public function handle()
    {
        $this->startQueueWorker();

        $finder = new Finder;
        $directories = $this->getWatchDirectories();

        if (empty($directories)) {
            $this->error('No directories to watch. The queue worker will run without file watching.');
            $this->monitorQueueWorker();

            return;
        }

        $finder->files()->in($directories)->name('*.php');

        $lastModified = $this->getLastModifiedTime($finder);

        while (true) {
            usleep(1000000);

            clearstatcache();

            $currentLastModified = $this->getLastModifiedTime($finder);

            if ($currentLastModified > $lastModified) {
                $this->info('File save detected. Restarting queue worker...');
                $this->stopQueueWorker();
                $this->startQueueWorker();
                $lastModified = $currentLastModified;
            }

            $this->monitorQueueWorker();
        }
    }

    protected function monitorQueueWorker()
    {
        if (! $this->process->isRunning()) {
            $this->error('Queue worker stopped unexpectedly. Restarting...');
            $this->startQueueWorker();
        }
    }

    protected function getWatchDirectories()
    {
        $directories = [];
        $possibleDirectories = [
            app_path('Jobs'),
            app_path('Events'),
            app_path('Listeners'),
        ];

        $possibleDirectories = config('queue-watch.directories') ?? $possibleDirectories;
        $possibleDirectories[] = base_path('config');

        foreach ($possibleDirectories as $dir) {
            if (is_dir($dir)) {
                $directories[] = $dir;
            }
        }

        return $directories;
    }

    protected function sleep($seconds)
    {
        sleep($seconds);
    }

    protected function startQueueWorker()
    {
        $command = ['php', 'artisan', 'queue:work'];

        $command = array_merge($command, $this->getQueueWorkArguments());

        $this->process = new Process($command);
        $this->process->setPty(true);
        $this->process->start(function ($type, $buffer) {
            $this->info($buffer);
        });

        $this->info('Queue worker started.');
    }

    protected function stopQueueWorker()
    {
        if ($this->process && $this->process->isRunning()) {
            $this->process->stop();
            $this->info('Queue worker stopped.');
        }
    }

    protected function getQueueWorkArguments()
    {
        $args = [];

        if ($connection = $this->argument('connection')) {
            $args[] = $connection;
        }

        $options = $this->options();
        if (is_array($options)) {
            foreach ($options as $key => $value) {
                if ($value === true) {
                    $args[] = "--{$key}";
                } elseif ($value !== false && $value !== null) {
                    $args[] = "--{$key}={$value}";
                }
            }
        }

        $args[] = '--verbose';

        return $args;
    }

    protected function getLastModifiedTime(Finder $finder)
    {
        $lastModified = 0;
        foreach ($finder as $file) {
            $lastModified = max($lastModified, $file->getMTime()); // Get the most recent modification time
        }

        return $lastModified;
    }
}
