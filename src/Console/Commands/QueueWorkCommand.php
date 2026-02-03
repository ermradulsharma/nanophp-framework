<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Nano\Framework\Queue\QueueManager;
use Nano\Framework\Queue\Worker;
use Nano\Framework\Database;
use Nano\Framework\LogManager;

class QueueWorkCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('queue:work')
            ->setDescription('Start processing jobs on the queue as a daemon')
            ->addOption('queue', null, InputOption::VALUE_OPTIONAL, 'The queue to listen on', 'default')
            ->addOption('sleep', null, InputOption::VALUE_OPTIONAL, 'Number of seconds to sleep when no job is available', 3)
            ->addOption('tries', null, InputOption::VALUE_OPTIONAL, 'Number of times to attempt a job before logging it failed', 1)
            ->addOption('timeout', null, InputOption::VALUE_OPTIONAL, 'The number of seconds a child process can run', 60)
            ->addOption('once', null, InputOption::VALUE_NONE, 'Only process the next job on the queue');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $queue = $input->getOption('queue');
        $sleep = (int) $input->getOption('sleep');
        $tries = (int) $input->getOption('tries');
        $timeout = (int) $input->getOption('timeout');
        $once = $input->getOption('once');

        $output->writeln("<info>Processing jobs from the '{$queue}' queue...</info>");

        // Get dependencies from container
        $database = app(Database::class);
        $logger = app(LogManager::class);
        $manager = app(QueueManager::class);

        $worker = new Worker($manager, $database, $logger);

        if ($once) {
            $job = $manager->connection()->pop($queue);

            if ($job) {
                $output->writeln("<comment>Processing job: {$job->getJobId()}</comment>");

                try {
                    $job->fire();
                    $job->delete();
                    $output->writeln("<info>Job processed successfully!</info>");
                } catch (\Exception $e) {
                    $output->writeln("<error>Job failed: {$e->getMessage()}</error>");

                    if ($job->attempts() >= $tries) {
                        $database->table('failed_jobs')->insert([
                            'connection' => 'database',
                            'queue' => $queue,
                            'payload' => $job->getRawBody(),
                            'exception' => (string) $e,
                            'failed_at' => time(),
                        ]);
                        $job->delete();
                    } else {
                        $job->release(60);
                    }
                }
            } else {
                $output->writeln("<comment>No jobs available.</comment>");
            }

            return Command::SUCCESS;
        }

        // Daemon mode
        $worker->daemon($queue, $sleep, $tries, $timeout);

        return Command::SUCCESS;
    }
}
