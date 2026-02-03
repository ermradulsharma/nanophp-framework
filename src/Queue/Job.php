<?php

namespace Nano\Framework\Queue;

use Nano\Framework\Queue\QueueManager;

abstract class Job
{
    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public ?string $queue = null;

    /**
     * The number of seconds before the job should be made available.
     *
     * @var \DateTimeInterface|\DateInterval|int|null
     */
    public \DateTimeInterface|\DateInterval|int|null $delay = null;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 1;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 60;

    /**
     * Indicates if the job should be encrypted.
     *
     * @var bool
     */
    public bool $encrypted = false;

    /**
     * The job instance.
     *
     * @var mixed
     */
    protected mixed $instance = null;

    /**
     * Dispatch the job with the given arguments.
     *
     * @return mixed
     */
    public static function dispatch(...$arguments): mixed
    {
        return static::dispatchNow(...$arguments);
    }

    /**
     * Dispatch the job immediately.
     *
     * @return mixed
     */
    public static function dispatchNow(...$arguments): mixed
    {
        $job = new static(...$arguments);

        if ($job instanceof ShouldQueue) {
            return app(QueueManager::class)->push($job);
        }

        return $job->handle();
    }

    /**
     * Set the desired queue for the job.
     *
     * @param string|null $queue
     * @return $this
     */
    public function onQueue(?string $queue): static
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * Set the desired delay for the job.
     *
     * @param \DateTimeInterface|\DateInterval|int|null $delay
     * @return $this
     */
    public function delay(\DateTimeInterface|\DateInterval|int|null $delay): static
    {
        $this->delay = $delay;
        return $this;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    abstract public function handle(): void;

    /**
     * Get the display name for the queued job.
     *
     * @return string
     */
    public function displayName(): string
    {
        return class_basename(static::class);
    }
}
