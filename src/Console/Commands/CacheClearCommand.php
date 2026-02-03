<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CacheClearCommand extends Command
{
    protected function configure()
    {
        $this->setName('cache:clear')
            ->setDescription('Flush the application cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cacheDirs = [
            __DIR__ . '/../../../../../../../storage/framework/cache/',
            __DIR__ . '/../../../../../../../storage/framework/views/',
        ];

        foreach ($cacheDirs as $dir) {
            if (!is_dir($dir)) continue;

            $files = glob($dir . '*');
            foreach ($files as $file) {
                if (is_file($file) && !str_ends_with($file, '.gitignore')) {
                    unlink($file);
                }
            }
        }

        $output->writeln("<info>Application cache cleared successfully!</info>");
        return Command::SUCCESS;
    }
}

