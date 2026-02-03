<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Nano\Framework\Console\Schedule;
use Nano\Framework\Application;

class ScheduleRunCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('schedule:run')
            ->setDescription('Run the scheduled commands');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // In a real framework, the schedule is defined in Console\Kernel or routes/console.php
        // For NanoPHP, let's look for a routes/console.php file or similar.

        $schedule = new Schedule();

        // Load schedule routes
        $consoleRoutes = base_path('routes/console.php');
        if (file_exists($consoleRoutes)) {
            require $consoleRoutes;
        }

        $events = $schedule->events();
        $ran = false;

        /** @var \Nano\Framework\Console\Scheduling\Event $event */
        foreach ($events as $event) {
            if ($event->isDue(app(Application::class))) {
                $output->writeln('<info>Running scheduled command:</info> ' . $event->command);

                // Execute the command
                // For simplicity we use exec/passthru but Process component is better
                // Using Symfony Process would be ideal
                $event->run(app(Application::class));

                $ran = true;
            }
        }

        if (!$ran) {
            $output->writeln('<info>No scheduled commands are ready to run.</info>');
        }

        return Command::SUCCESS;
    }
}
