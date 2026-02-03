<?php

namespace Nano\Framework\Queue;

use Nano\Framework\Contracts\QueueInterface;
use Nano\Framework\Database;
use Nano\Framework\Queue\DatabaseQueue;

class QueueManager
{
    /**
     * The database instance.
     *
     * @var \Nano\Framework\Database
     */
    protected Database $database;

    /**
     * The array of resolved queue connections.
     *
     * @var array
     */
    protected array $connections = [];

    /**
     * The default queue connection name.
     *
     * @var string
     */
    protected string $default = 'database';

    /**
     * Create a new queue manager instance.
     *
     * @param \Nano\Framework\Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Get a queue connection instance.
     *
     * @param string|null $name
     * @return \Nano\Framework\Contracts\QueueInterface
     */
    public function connection(?string $name = null): QueueInterface
    {
        $name = $name ?: $this->default;

        if (!isset($this->connections[$name])) {
            $this->connections[$name] = $this->resolve($name);
        }

        return $this->connections[$name];
    }

    /**
     * Resolve a queue connection.
     *
     * @param string $name
     * @return \Nano\Framework\Contracts\QueueInterface
     */
    protected function resolve(string $name): QueueInterface
    {
        // For now, we only support database driver
        // In future, you can add Redis, SQS, etc.
        return new DatabaseQueue($this->database);
    }

    /**
     * Push a new job onto the queue.
     *
     * @param string|object $job
     * @param mixed $data
     * @param string|null $queue
     * @param string|null $connection
     * @return mixed
     */
    public function push(string|object $job, mixed $data = '', ?string $queue = null, ?string $connection = null): mixed
    {
        if (is_object($job)) {
            $jobClass = get_class($job);
            $data = $job;
            $queue = $job->queue ?? $queue;

            if (isset($job->delay) && $job->delay) {
                return $this->connection($connection)->later($job->delay, $jobClass, $data, $queue);
            }
        }

        return $this->connection($connection)->push(is_object($job) ? get_class($job) : $job, $data, $queue);
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param \DateTimeInterface|\DateInterval|int $delay
     * @param string|object $job
     * @param mixed $data
     * @param string|null $queue
     * @param string|null $connection
     * @return mixed
     */
    public function later(\DateTimeInterface|\DateInterval|int $delay, string|object $job, mixed $data = '', ?string $queue = null, ?string $connection = null): mixed
    {
        return $this->connection($connection)->later(
            $delay,
            is_object($job) ? get_class($job) : $job,
            is_object($job) ? $job : $data,
            $queue
        );
    }

    /**
     * Get the size of the queue.
     *
     * @param string|null $queue
     * @param string|null $connection
     * @return int
     */
    public function size(?string $queue = null, ?string $connection = null): int
    {
        return $this->connection($connection)->size($queue);
    }

    /**
     * Set the default queue connection name.
     *
     * @param string $name
     * @return void
     */
    public function setDefaultConnection(string $name): void
    {
        $this->default = $name;
    }

    /**
     * Get the default queue connection name.
     *
     * @return string
     */
    public function getDefaultConnection(): string
    {
        return $this->default;
    }

    /**
     * Dynamically pass calls to the default connection.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->connection()->$method(...$parameters);
    }
}
