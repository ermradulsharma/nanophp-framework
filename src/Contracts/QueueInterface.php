<?php

namespace Nano\Framework\Contracts;

interface QueueInterface
{
    /**
     * Push a new job onto the queue.
     *
     * @param string $job
     * @param mixed $data
     * @param string|null $queue
     * @return mixed
     */
    public function push(string $job, mixed $data = '', ?string $queue = null): mixed;

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param \DateTimeInterface|\DateInterval|int $delay
     * @param string $job
     * @param mixed $data
     * @param string|null $queue
     * @return mixed
     */
    public function later(\DateTimeInterface|\DateInterval|int $delay, string $job, mixed $data = '', ?string $queue = null): mixed;

    /**
     * Pop the next job off of the queue.
     *
     * @param string|null $queue
     * @return \Nano\Framework\Queue\JobContract|null
     */
    public function pop(?string $queue = null): ?\Nano\Framework\Queue\JobContract;

    /**
     * Delete a reserved job from the queue.
     *
     * @param string $queue
     * @param string $id
     * @return void
     */
    public function deleteReserved(string $queue, string $id): void;

    /**
     * Release a reserved job back onto the queue.
     *
     * @param string $queue
     * @param string $id
     * @param int $delay
     * @return void
     */
    public function release(string $queue, string $id, int $delay = 0): void;
    /**
     * Get the size of the queue.
     *
     * @param string|null $queue
     * @return int
     */
    public function size(?string $queue = null): int;
}
