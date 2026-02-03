<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigClearCommand extends Command
{
    protected function configure()
    {
        $this->setName('config:clear')
            ->setDescription('Remove the configuration cache file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cachePath = __DIR__ . '/../../../../../../../storage/framework/cache/config.php';

        if (file_exists($cachePath)) {
            unlink($cachePath);
            $output->writeln("<info>Configuration cache cleared!</info>");
        } else {
            $output->writeln("<comment>No configuration cache found.</comment>");
        }

        return Command::SUCCESS;
    }
}

