<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Nano\Framework\Database;

class QueueFailedCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('queue:failed')
            ->setDescription('List all of the failed queue jobs');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $database = app(Database::class);

        $failedJobs = $database->table('failed_jobs')
            ->orderBy('failed_at', 'DESC')
            ->get();

        if (empty($failedJobs)) {
            $output->writeln('<info>No failed jobs!</info>');
            return Command::SUCCESS;
        }

        $output->writeln('');
        $output->writeln('<comment>Failed Jobs:</comment>');
        $output->writeln('');

        foreach ($failedJobs as $job) {
            $output->writeln("ID: <info>{$job->id}</info>");
            $output->writeln("Queue: {$job->queue}");
            $output->writeln("Failed At: " . date('Y-m-d H:i:s', $job->failed_at));
            $output->writeln("Exception: " . substr($job->exception, 0, 100) . '...');
            $output->writeln('---');
        }

        $output->writeln('');
        $output->writeln("Total failed jobs: <info>" . count($failedJobs) . "</info>");
        $output->writeln('');

        return Command::SUCCESS;
    }
}
