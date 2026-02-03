<?php

namespace Nano\Framework\Queue;

use Nano\Framework\Database;

class DatabaseJob implements JobContract
{
    /**
     * The database instance.
     *
     * @var \Nano\Framework\Database
     */
    protected Database $database;

    /**
     * The database job payload.
     *
     * @var object
     */
    protected object $job;

    /**
     * The queue name.
     *
     * @var string
     */
    protected string $queue;

    /**
     * Indicates if the job has been deleted.
     *
     * @var bool
     */
    protected bool $deleted = false;

    /**
     * Create a new database job instance.
     *
     * @param \Nano\Framework\Database $database
     * @param object $job
     * @param string $queue
     */
    public function __construct(Database $database, object $job, string $queue)
    {
        $this->database = $database;
        $this->job = $job;
        $this->queue = $queue;
    }

    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId(): string
    {
        return (string) $this->job->id;
    }

    /**
     * Get the raw body of the job.
     *
     * @return string
     */
    public function getRawBody(): string
    {
        return $this->job->payload;
    }

    /**
     * Fire the job.
     *
     * @return void
     */
    public function fire(): void
    {
        $payload = json_decode($this->job->payload, true);

        $class = $payload['job'] ?? null;
        $data = $payload['data'] ?? [];

        if ($class && class_exists($class)) {
            $instance = unserialize($data);

            if (method_exists($instance, 'handle')) {
                $instance->handle();
            }
        }
    }

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete(): void
    {
        $this->database->table('jobs')
            ->where('id', $this->job->id)
            ->delete();

        $this->deleted = true;
    }

    /**
     * Release the job back into the queue.
     *
     * @param int $delay
     * @return void
     */
    public function release(int $delay = 0): void
    {
        $this->database->table('jobs')
            ->where('id', $this->job->id)
            ->update([
                'attempts' => $this->job->attempts + 1,
                'reserved_at' => null,
                'available_at' => time() + $delay,
            ]);

        $this->deleted = true;
    }

    /**
     * Determine if the job has been deleted.
     *
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts(): int
    {
        return (int) $this->job->attempts;
    }

    /**
     * Get the job instance.
     *
     * @return object
     */
    public function getJob(): object
    {
        return $this->job;
    }
}
