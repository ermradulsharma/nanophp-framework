<?php

namespace Nano\Framework\Queue;

use Nano\Framework\Contracts\QueueInterface;
use Nano\Framework\Database;

class DatabaseQueue implements QueueInterface
{
    /**
     * The database instance.
     *
     * @var \Nano\Framework\Database
     */
    protected Database $database;

    /**
     * The name of the default queue.
     *
     * @var string
     */
    protected string $default;

    /**
     * The expiration time of a job.
     *
     * @var int|null
     */
    protected ?int $retryAfter = 60;

    /**
     * Create a new database queue instance.
     *
     * @param \Nano\Framework\Database $database
     * @param string $default
     * @param int $retryAfter
     */
    public function __construct(Database $database, string $default = 'default', int $retryAfter = 60)
    {
        $this->database = $database;
        $this->default = $default;
        $this->retryAfter = $retryAfter;
    }

    /**
     * Push a new job onto the queue.
     *
     * @param string $job
     * @param mixed $data
     * @param string|null $queue
     * @return mixed
     */
    public function push(string $job, mixed $data = '', ?string $queue = null): mixed
    {
        return $this->pushToDatabase($queue, $this->createPayload($job, $data));
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param \DateTimeInterface|\DateInterval|int $delay
     * @param string $job
     * @param mixed $data
     * @param string|null $queue
     * @return mixed
     */
    public function later(\DateTimeInterface|\DateInterval|int $delay, string $job, mixed $data = '', ?string $queue = null): mixed
    {
        return $this->pushToDatabase($queue, $this->createPayload($job, $data), $delay);
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param string|null $queue
     * @return \Nano\Framework\Queue\JobContract|null
     */
    public function pop(?string $queue = null): ?JobContract
    {
        $queue = $this->getQueue($queue);

        return $this->database->transaction(function () use ($queue) {
            if ($job = $this->getNextAvailableJob($queue)) {
                return $this->marshalJob($queue, $job);
            }

            return null;
        });
    }

    /**
     * Get the next available job for the queue.
     *
     * @param string $queue
     * @return object|null
     */
    protected function getNextAvailableJob(string $queue): ?object
    {
        $job = $this->database->table('jobs')
            ->where('queue', $queue)
            ->where(function ($query) {
                $query->whereNull('reserved_at')
                    ->orWhere('reserved_at', '<=', time() - $this->retryAfter);
            })
            ->where('available_at', '<=', time())
            ->orderBy('id', 'ASC')
            ->first();

        if ($job) {
            $this->database->table('jobs')
                ->where('id', $job->id)
                ->update([
                    'reserved_at' => time(),
                    'attempts' => $job->attempts + 1,
                ]);

            // Fetch the updated job
            return $this->database->table('jobs')
                ->where('id', $job->id)
                ->first();
        }

        return null;
    }

    /**
     * Marshal the reserved job into a DatabaseJob instance.
     *
     * @param string $queue
     * @param object $job
     * @return \Nano\Framework\Queue\DatabaseJob
     */
    protected function marshalJob(string $queue, object $job): DatabaseJob
    {
        return new DatabaseJob($this->database, $job, $queue);
    }

    /**
     * Delete a reserved job from the queue.
     *
     * @param string $queue
     * @param string $id
     * @return void
     */
    public function deleteReserved(string $queue, string $id): void
    {
        $this->database->table('jobs')
            ->where('id', $id)
            ->delete();
    }

    /**
     * Release a reserved job back onto the queue.
     *
     * @param string $queue
     * @param string $id
     * @param int $delay
     * @return void
     */
    public function release(string $queue, string $id, int $delay = 0): void
    {
        $this->database->table('jobs')
            ->where('id', $id)
            ->update([
                'reserved_at' => null,
                'available_at' => time() + $delay,
            ]);
    }

    /**
     * Push a raw payload to the database.
     *
     * @param string|null $queue
     * @param string $payload
     * @param \DateTimeInterface|\DateInterval|int $delay
     * @return mixed
     */
    protected function pushToDatabase(?string $queue, string $payload, \DateTimeInterface|\DateInterval|int $delay = 0): mixed
    {
        $availableAt = $this->availableAt($delay);

        return $this->database->table('jobs')->insertGetId([
            'queue' => $this->getQueue($queue),
            'payload' => $payload,
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => $availableAt,
            'created_at' => time(),
        ]);
    }

    /**
     * Create a payload string from the given job and data.
     *
     * @param string $job
     * @param mixed $data
     * @return string
     */
    protected function createPayload(string $job, mixed $data): string
    {
        return json_encode([
            'job' => $job,
            'data' => is_object($data) ? serialize($data) : $data,
            'attempts' => 0,
        ]);
    }

    /**
     * Get the "available at" UNIX timestamp.
     *
     * @param \DateTimeInterface|\DateInterval|int $delay
     * @return int
     */
    protected function availableAt(\DateTimeInterface|\DateInterval|int $delay = 0): int
    {
        if ($delay instanceof \DateTimeInterface) {
            return $delay->getTimestamp();
        }

        if ($delay instanceof \DateInterval) {
            return (new \DateTime())->add($delay)->getTimestamp();
        }

        return time() + $delay;
    }

    /**
     * Get the queue or return the default.
     *
     * @param string|null $queue
     * @return string
     */
    protected function getQueue(?string $queue): string
    {
        return $queue ?: $this->default;
    }

    /**
     * Get the size of the queue.
     *
     * @param string|null $queue
     * @return int
     */
    public function size(?string $queue = null): int
    {
        return $this->database->table('jobs')
            ->where('queue', $this->getQueue($queue))
            ->count();
    }
}
