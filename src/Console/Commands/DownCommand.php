<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DownCommand extends Command
{
    protected function configure()
    {
        $this->setName('down')
            ->setDescription('Put the application into maintenance mode');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = __DIR__ . '/../../../../../../../storage/framework/down';

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, json_encode([
            'time' => time(),
            'message' => 'The application is down for maintenance.'
        ]));

        $output->writeln("<info>Application is now in maintenance mode.</info>");

        return Command::SUCCESS;
    }
}

