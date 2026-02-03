<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Nano\Framework\Database;

class QueueRetryCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('queue:retry')
            ->setDescription('Retry a failed queue job')
            ->addArgument('id', InputArgument::REQUIRED, 'The ID of the failed job');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument('id');
        $database = app(Database::class);

        $failedJob = $database->table('failed_jobs')
            ->where('id', $id)
            ->first();

        if (!$failedJob) {
            $output->writeln("<error>Failed job [{$id}] not found!</error>");
            return Command::FAILURE;
        }

        // Re-push the job to the queue
        $database->table('jobs')->insert([
            'queue' => $failedJob->queue,
            'payload' => $failedJob->payload,
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => time(),
            'created_at' => time(),
        ]);

        // Delete from failed jobs
        $database->table('failed_jobs')
            ->where('id', $id)
            ->delete();

        $output->writeln("<info>Failed job [{$id}] has been pushed back onto the queue!</info>");

        return Command::SUCCESS;
    }
}
