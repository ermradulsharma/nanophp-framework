<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Nano\Framework\Database;

class QueueFlushCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('queue:flush')
            ->setDescription('Flush all of the failed queue jobs');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $database = app(Database::class);

        $count = $database->table('failed_jobs')->count();

        if ($count === 0) {
            $output->writeln('<info>No failed jobs to flush!</info>');
            return Command::SUCCESS;
        }

        $database->table('failed_jobs')->delete();

        $output->writeln("<info>Flushed {$count} failed jobs!</info>");

        return Command::SUCCESS;
    }
}
