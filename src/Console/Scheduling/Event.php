<?php

namespace Nano\Framework\Console\Scheduling;

use Cron\CronExpression;
use Nano\Framework\Application;

class Event
{
    /**
     * The command string.
     *
     * @var string
     */
    public string $command;

    /**
     * The cron expression representing the event's frequency.
     *
     * @var string
     */
    public string $expression = '* * * * *';

    /**
     * The timezone the date should be evaluated on.
     *
     * @var \DateTimeZone|string
     */
    public $timezone;

    /**
     * The array of filter callbacks.
     *
     * @var array
     */
    protected array $filters = [];

    /**
     * The array of reject callbacks.
     *
     * @var array
     */
    protected array $rejects = [];

    /**
     * The human readable description of the event.
     *
     * @var string
     */
    public string $description = '';

    /**
     * Create a new event instance.
     *
     * @param string $command
     */
    public function __construct(string $command)
    {
        $this->command = $command;
    }

    /**
     * Determine if the event is due to run.
     *
     * @param \Nano\Framework\Application $app
     * @return bool
     */
    public function isDue(Application $app): bool
    {
        $date = new \DateTime('now', new \DateTimeZone($this->timezone ?: 'UTC'));

        return (new CronExpression($this->expression))->isDue($date);
    }

    /**
     * Schedule the event to run daily.
     *
     * @return $this
     */
    public function daily(): static
    {
        return $this->cron('0 0 * * *');
    }

    /**
     * Schedule the event to run at a given time (10:00).
     *
     * @param string $time
     * @return $this
     */
    public function dailyAt(string $time): static
    {
        $segments = explode(':', $time);
        return $this->cron(sprintf('%d %d * * *', (int) $segments[1], (int) $segments[0]));
    }

    /**
     * Schedule the event to run daily at two times (1:00 & 13:00).
     *
     * @param string $first
     * @param string $second
     * @return $this
     */
    public function twiceDaily(int $first = 1, int $second = 13): static
    {
        return $this->cron(sprintf('0 %d,%d * * *', $first, $second));
    }

    /**
     * Schedule the event to run only on weekdays.
     *
     * @return $this
     */
    public function weekdays(): static
    {
        return $this->cron('* * * * 1-5');
    }

    /**
     * Schedule the event to run only on weekends.
     *
     * @return $this
     */
    public function weekends(): static
    {
        return $this->cron('* * * * 0,6');
    }

    /**
     * Schedule the event to run on Mondays.
     *
     * @return $this
     */
    public function mondays(): static
    {
        return $this->cron('* * * * 1');
    }

    /**
     * Schedule the event to run on Tuesdays.
     *
     * @return $this
     */
    public function tuesdays(): static
    {
        return $this->cron('* * * * 2');
    }

    /**
     * Schedule the event to run on Wednesdays.
     *
     * @return $this
     */
    public function wednesdays(): static
    {
        return $this->cron('* * * * 3');
    }

    /**
     * Schedule the event to run on Thursdays.
     *
     * @return $this
     */
    public function thursdays(): static
    {
        return $this->cron('* * * * 4');
    }

    /**
     * Schedule the event to run on Fridays.
     *
     * @return $this
     */
    public function fridays(): static
    {
        return $this->cron('* * * * 5');
    }

    /**
     * Schedule the event to run on Saturdays.
     *
     * @return $this
     */
    public function saturdays(): static
    {
        return $this->cron('* * * * 6');
    }

    /**
     * Schedule the event to run on Sundays.
     *
     * @return $this
     */
    public function sundays(): static
    {
        return $this->cron('* * * * 0');
    }

    /**
     * Schedule the event to run weekly.
     *
     * @return $this
     */
    public function weekly(): static
    {
        return $this->cron('0 0 * * 0');
    }

    /**
     * Schedule the event to run weekly on a given day and time.
     *
     * @param int|string $day
     * @param string $time
     * @return $this
     */
    public function weeklyOn(int|string $day, string $time = '0:0'): static
    {
        $segments = explode(':', $time);
        return $this->cron(sprintf('%d %d * * %s', (int) $segments[1], (int) $segments[0], $day));
    }

    /**
     * Schedule the event to run monthly.
     *
     * @return $this
     */
    public function monthly(): static
    {
        return $this->cron('0 0 1 * *');
    }

    /**
     * Schedule the event to run monthly on a given day and time.
     *
     * @param int $day
     * @param string $time
     * @return $this
     */
    public function monthlyOn(int $day = 1, string $time = '0:0'): static
    {
        $segments = explode(':', $time);
        return $this->cron(sprintf('%d %d %d * *', (int) $segments[1], (int) $segments[0], $day));
    }

    /**
     * Schedule the event to run twice monthly.
     *
     * @param int $first
     * @param int $second
     * @param string $time
     * @return $this
     */
    public function twiceMonthly(int $first = 1, int $second = 16, string $time = '0:0'): static
    {
        $segments = explode(':', $time);
        return $this->cron(sprintf('%d %d %d,%d * *', (int) $segments[1], (int) $segments[0], $first, $second));
    }

    /**
     * Schedule the event to run quarterly.
     *
     * @return $this
     */
    public function quarterly(): static
    {
        return $this->cron('0 0 1 */3 *');
    }

    /**
     * Schedule the event to run yearly.
     *
     * @return $this
     */
    public function yearly(): static
    {
        return $this->cron('0 0 1 1 *');
    }

    /**
     * Set the timezone the date should be evaluated on.
     *
     * @param \DateTimeZone|string $timezone
     * @return $this
     */
    public function timezone($timezone): static
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * The Cron expression representing the event's frequency.
     *
     * @param string $expression
     * @return $this
     */
    public function cron(string $expression): static
    {
        $this->expression = $expression;
        return $this;
    }

    /**
     * Schedule the event to run every minute.
     *
     * @return $this
     */
    public function everyMinute(): static
    {
        return $this->cron('* * * * *');
    }

    /**
     * Schedule the event to run every two minutes.
     *
     * @return $this
     */
    public function everyTwoMinutes(): static
    {
        return $this->cron('*/2 * * * *');
    }

    /**
     * Schedule the event to run every three minutes.
     *
     * @return $this
     */
    public function everyThreeMinutes(): static
    {
        return $this->cron('*/3 * * * *');
    }

    /**
     * Schedule the event to run every four minutes.
     *
     * @return $this
     */
    public function everyFourMinutes(): static
    {
        return $this->cron('*/4 * * * *');
    }

    /**
     * Schedule the event to run every five minutes.
     *
     * @return $this
     */
    public function everyFiveMinutes(): static
    {
        return $this->cron('*/5 * * * *');
    }

    /**
     * Schedule the event to run every ten minutes.
     *
     * @return $this
     */
    public function everyTenMinutes(): static
    {
        return $this->cron('*/10 * * * *');
    }

    /**
     * Schedule the event to run every fifteen minutes.
     *
     * @return $this
     */
    public function everyFifteenMinutes(): static
    {
        return $this->cron('*/15 * * * *');
    }

    /**
     * Schedule the event to run every thirty minutes.
     *
     * @return $this
     */
    public function everyThirtyMinutes(): static
    {
        return $this->cron('0,30 * * * *');
    }

    /**
     * Schedule the event to run hourly.
     *
     * @return $this
     */
    public function hourly(): static
    {
        return $this->cron('0 * * * *');
    }

    /**
     * Schedule the event to run hourly at a given offset in the hour.
     *
     * @param int|array $offset
     * @return $this
     */
    public function hourlyAt(int|array $offset): static
    {
        $offset = is_array($offset) ? implode(',', $offset) : $offset;
        return $this->cron("{$offset} * * * *");
    }

    /**
     * Schedule the event to run every two hours.
     *
     * @return $this
     */
    public function everyTwoHours(): static
    {
        return $this->cron('0 */2 * * *');
    }

    /**
     * Schedule the event to run every three hours.
     *
     * @return $this
     */
    public function everyThreeHours(): static
    {
        return $this->cron('0 */3 * * *');
    }

    /**
     * Schedule the event to run every four hours.
     *
     * @return $this
     */
    public function everyFourHours(): static
    {
        return $this->cron('0 */4 * * *');
    }

    /**
     * Schedule the event to run every six hours.
     *
     * @return $this
     */
    public function everySixHours(): static
    {
        return $this->cron('0 */6 * * *');
    }

    /**
     * Schedule the event to run between start and end times.
     *
     * @param string $startTime
     * @param string $endTime
     * @return $this
     */
    public function between(string $startTime, string $endTime): static
    {
        return $this->when($this->inTimeInterval($startTime, $endTime));
    }

    /**
     * Schedule the event to not run between start and end times.
     *
     * @param string $startTime
     * @param string $endTime
     * @return $this
     */
    public function unlessBetween(string $startTime, string $endTime): static
    {
        return $this->skip($this->inTimeInterval($startTime, $endTime));
    }

    /**
     * Determine if the event should run based on a truth-test closure.
     *
     * @param \Closure|bool $callback
     * @return $this
     */
    public function when(\Closure|bool $callback): static
    {
        $this->filters[] = is_callable($callback) ? $callback : function () use ($callback) {
            return $callback;
        };

        return $this;
    }

    /**
     * Determine if the event should skip based on a truth-test closure.
     *
     * @param \Closure|bool $callback
     * @return $this
     */
    public function skip(\Closure|bool $callback): static
    {
        $this->rejects[] = is_callable($callback) ? $callback : function () use ($callback) {
            return $callback;
        };

        return $this;
    }

    /**
     * Check if the current time is within the given interval.
     *
     * @param string $startTime
     * @param string $endTime
     * @return \Closure
     */
    protected function inTimeInterval(string $startTime, string $endTime): \Closure
    {
        return function () use ($startTime, $endTime) {
            $now = date('H:i');

            if ($endTime < $startTime) {
                return $now >= $startTime || $now <= $endTime;
            }

            return $now >= $startTime && $now <= $endTime;
        };
    }

    /**
     * Run the given event.
     *
     * @param \Nano\Framework\Application $app
     * @return void
     */
    public function run(Application $app): void
    {
        // Check filters
        foreach ($this->filters as $filter) {
            if (! $filter($app)) {
                return;
            }
        }

        // Check rejects
        foreach ($this->rejects as $reject) {
            if ($reject($app)) {
                return;
            }
        }

        // Simple execution for now
        $command = $this->command;

        exec($command . ' > /dev/null 2>&1 &');
    }

    /**
     * Set the human readable description of the event.
     *
     * @param string $description
     * @return $this
     */
    public function description(string $description): static
    {
        $this->description = $description;
        return $this;
    }
}
