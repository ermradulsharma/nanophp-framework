<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Nano\Framework\Console\Schedule;

class ScheduleListCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('schedule:list')
            ->setDescription('List the scheduled commands');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $schedule = new Schedule();

        // Load schedule routes
        $consoleRoutes = base_path('routes/console.php');
        if (file_exists($consoleRoutes)) {
            require $consoleRoutes;
        }

        $events = $schedule->events();

        if (empty($events)) {
            $output->writeln('<info>No scheduled commands found.</info>');
            return Command::SUCCESS;
        }

        $rows = [];
        foreach ($events as $event) {
            $rows[] = [
                $event->expression,
                $event->command,
                $event->description,
                (new \Cron\CronExpression($event->expression))->getNextRunDate()->format('Y-m-d H:i:s')
            ];
        }

        $table = new Table($output);
        $table
            ->setHeaders(['Interval', 'Command', 'Description', 'Next Run'])
            ->setRows($rows);

        $table->render();

        return Command::SUCCESS;
    }
}
