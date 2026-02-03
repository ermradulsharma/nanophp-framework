<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Nano\Framework\Database;

class QueueRestartCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('queue:restart')
            ->setDescription('Restart queue worker daemons after their current job');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Create a restart signal file
        $restartFile = __DIR__ . '/../../../../storage/framework/queue.restart';

        $dir = dirname($restartFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        touch($restartFile);

        $output->writeln('<info>Broadcasting queue restart signal.</info>');
        $output->writeln('<comment>Workers will restart after completing their current job.</comment>');

        return Command::SUCCESS;
    }
}
