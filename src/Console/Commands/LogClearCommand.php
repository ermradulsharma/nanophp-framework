<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LogClearCommand extends Command
{
    protected function configure()
    {
        $this->setName('log:clear')
            ->setDescription('Clear all log files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logPath = __DIR__ . '/../../../../../../../storage/logs/nano.log';

        if (file_exists($logPath)) {
            file_put_contents($logPath, '');
            $output->writeln("<info>Logs cleared!</info>");
        } else {
            $output->writeln("<comment>No log file found.</comment>");
        }

        return Command::SUCCESS;
    }
}

