<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class ServeCommand extends Command
{
    protected function configure()
    {
        $this->setName('serve')
            ->setDescription('Serve the application on the PHP development server')
            ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'The port to serve the application on', 8000)
            ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'The host address to serve the application on', 'localhost');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $port = $input->getOption('port');
        $host = $input->getOption('host');

        $output->writeln("<info>NanoPHP development server started:</info> <http://{$host}:{$port}>");
        $output->writeln("Press Ctrl+C to stop the server");

        passthru("php -S {$host}:{$port} -t public");

        return Command::SUCCESS;
    }
}

