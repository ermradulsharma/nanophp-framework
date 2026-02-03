<?php

namespace Nano\Framework\Console;

use Nano\Framework\Console\Scheduling\Event;

class Schedule
{
    /**
     * All of the events on the schedule.
     *
     * @var array
     */
    protected array $events = [];

    /**
     * Add a new command event to the schedule.
     *
     * @param string $command
     * @param array $parameters
     * @return \Nano\Framework\Console\Scheduling\Event
     */
    public function command(string $command, array $parameters = []): Event
    {
        if (class_exists($command)) {
            // If it's a class name, usage might be: $schedule->command(MyCommand::class)
            // But usually we invoke by signature. For now let's assume raw command string or "php artisan" wrapper
            // If it's internal command, we might just use the signature.
            // Let's assume standard "command:name" string.
            // If parameters, append them.
        }

        return $this->exec($command, $parameters);
    }

    /**
     * Add a new Artisan command event to the schedule.
     * 
     * @param string $command
     * @param array $parameters
     * @return \Nano\Framework\Console\Scheduling\Event
     */
    public function artisan(string $command, array $parameters = []): Event
    {
        $binary = 'php artisan';

        $cmd = "{$binary} {$command}";

        return $this->exec($cmd, $parameters);
    }

    /**
     * Add a new callback event to the schedule.
     *
     * @param string|callable $callback
     * @param array $parameters
     * @return \Nano\Framework\Console\Scheduling\Event
     */
    public function call(string|callable $callback, array $parameters = []): Event
    {
        // For closures, we cannot easily serialize them to run in background process 
        // unless we use a specialized runner. 
        // For simplicity in Phase 1, we might only support string commands or 
        // simple closures that run in the same process (if schedule:run is foreground).

        // For now, let's treat it as a special "callback" event type if we can,
        // but typically Laravel serializes closures or uses `Opis\Closure`.

        // Let's stick to command strings for robust implementation first,
        // or just accept valid callables that are static methods.

        // Placeholder implementation
        return $this->events[] = new Event('closure');
    }

    /**
     * Add a new command event to the schedule.
     *
     * @param string $command
     * @param array $parameters
     * @return \Nano\Framework\Console\Scheduling\Event
     */
    public function exec(string $command, array $parameters = []): Event
    {
        if (count($parameters)) {
            $command .= ' ' . $this->compileParameters($parameters);
        }

        $this->events[] = $event = new Event($command);

        return $event;
    }

    /**
     * Compile parameters for a command.
     *
     * @param array $parameters
     * @return string
     */
    protected function compileParameters(array $parameters): string
    {
        return collect($parameters)->map(function ($value, $key) {
            if (is_array($value)) {
                return $this->compileParameters($value);
            }

            if (! is_numeric($key)) {
                return $value; // --option=value logic needed here really
            }

            return $value;
        })->implode(' ');
    }

    /**
     * Get all of the events on the schedule.
     *
     * @return array
     */
    public function events(): array
    {
        return $this->events;
    }
}
