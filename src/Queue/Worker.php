<?php

namespace Nano\Framework\Queue;

use Nano\Framework\Database;
use Nano\Framework\LogManager;
use Exception;

class Worker
{
    /**
     * The queue manager instance.
     *
     * @var \Nano\Framework\Queue\QueueManager
     */
    protected QueueManager $manager;

    /**
     * The database instance.
     *
     * @var \Nano\Framework\Database
     */
    protected Database $database;

    /**
     * The log manager instance.
     *
     * @var \Nano\Framework\LogManager
     */
    protected LogManager $logger;

    /**
     * Indicates if the worker should exit.
     *
     * @var bool
     */
    protected bool $shouldQuit = false;

    /**
     * Create a new queue worker.
     *
     * @param \Nano\Framework\Queue\QueueManager $manager
     * @param \Nano\Framework\Database $database
     * @param \Nano\Framework\LogManager $logger
     */
    public function __construct(QueueManager $manager, Database $database, LogManager $logger)
    {
        $this->manager = $manager;
        $this->database = $database;
        $this->logger = $logger;
    }

    /**
     * Listen to the given queue in a loop.
     *
     * @param string|null $queue
     * @param int $sleep
     * @param int $maxTries
     * @param int $timeout
     * @return void
     */
    public function daemon(?string $queue = null, int $sleep = 3, int $maxTries = 1, int $timeout = 60): void
    {
        $this->listenForSignals();

        while (!$this->shouldQuit) {
            $job = $this->getNextJob($queue);

            if ($job) {
                $this->process($job, $maxTries, $timeout);
            } else {
                $this->sleep($sleep);
            }
        }
    }

    /**
     * Get the next job from the queue.
     *
     * @param string|null $queue
     * @return \Nano\Framework\Queue\JobContract|null
     */
    protected function getNextJob(?string $queue): ?JobContract
    {
        try {
            return $this->manager->connection()->pop($queue);
        } catch (Exception $e) {
            $this->logger->error('Failed to pop job from queue: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Process the given job.
     *
     * @param \Nano\Framework\Queue\JobContract $job
     * @param int $maxTries
     * @param int $timeout
     * @return void
     */
    protected function process(JobContract $job, int $maxTries, int $timeout): void
    {
        try {
            $this->logger->info('Processing job: ' . $job->getJobId());

            // Set timeout
            set_time_limit($timeout);

            // Fire the job
            $job->fire();

            // Delete the job if successful
            $job->delete();

            $this->logger->info('Job processed successfully: ' . $job->getJobId());
        } catch (Exception $e) {
            $this->handleJobException($job, $e, $maxTries);
        }
    }

    /**
     * Handle an exception that occurred while processing a job.
     *
     * @param \Nano\Framework\Queue\JobContract $job
     * @param \Exception $e
     * @param int $maxTries
     * @return void
     */
    protected function handleJobException(JobContract $job, Exception $e, int $maxTries): void
    {
        $this->logger->error('Job failed: ' . $job->getJobId() . ' - ' . $e->getMessage());

        if ($job->attempts() >= $maxTries) {
            $this->logFailedJob($job, $e);
            $job->delete();
        } else {
            $job->release(60); // Release back to queue with 60 second delay
        }
    }

    /**
     * Log a failed job into the failed_jobs table.
     *
     * @param \Nano\Framework\Queue\JobContract $job
     * @param \Exception $e
     * @return void
     */
    protected function logFailedJob(JobContract $job, Exception $e): void
    {
        try {
            $this->database->table('failed_jobs')->insert([
                'connection' => 'database',
                'queue' => 'default',
                'payload' => $job->getRawBody(),
                'exception' => (string) $e,
                'failed_at' => time(),
            ]);
        } catch (Exception $ex) {
            $this->logger->error('Failed to log failed job: ' . $ex->getMessage());
        }
    }

    /**
     * Sleep the script for a given number of seconds.
     *
     * @param int $seconds
     * @return void
     */
    protected function sleep(int $seconds): void
    {
        sleep($seconds);
    }

    /**
     * Listen for signals to gracefully shutdown.
     *
     * @return void
     */
    protected function listenForSignals(): void
    {
        if (extension_loaded('pcntl')) {
            pcntl_signal(SIGTERM, function () {
                $this->shouldQuit = true;
            });

            pcntl_signal(SIGINT, function () {
                $this->shouldQuit = true;
            });
        }
    }

    /**
     * Stop the worker.
     *
     * @return void
     */
    public function stop(): void
    {
        $this->shouldQuit = true;
    }

    /**
     * Determine if the worker should process jobs.
     *
     * @return bool
     */
    public function shouldQuit(): bool
    {
        return $this->shouldQuit;
    }
}
