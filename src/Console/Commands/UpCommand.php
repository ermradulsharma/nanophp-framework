<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpCommand extends Command
{
    protected function configure()
    {
        $this->setName('up')
            ->setDescription('Bring the application out of maintenance mode');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = __DIR__ . '/../../../../../../../storage/framework/down';

        if (file_exists($path)) {
            unlink($path);
            $output->writeln("<info>Application is now live.</info>");
        } else {
            $output->writeln("<comment>Application is already live.</comment>");
        }

        return Command::SUCCESS;
    }
}

