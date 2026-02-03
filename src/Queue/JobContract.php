<?php

namespace Nano\Framework\Queue;

interface JobContract
{
    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId(): string;

    /**
     * Get the raw body of the job.
     *
     * @return string
     */
    public function getRawBody(): string;

    /**
     * Fire the job.
     *
     * @return void
     */
    public function fire(): void;

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete(): void;

    /**
     * Release the job back into the queue.
     *
     * @param int $delay
     * @return void
     */
    public function release(int $delay = 0): void;

    /**
     * Determine if the job has been deleted.
     *
     * @return bool
     */
    public function isDeleted(): bool;

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts(): int;
}
