<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Filesystem\Filesystem;

class ViewClearCommand extends Command
{
    protected function configure()
    {
        $this->setName('view:clear')
            ->setDescription('Clear all compiled view files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = __DIR__ . '/../../../../../../../storage/framework/views';
        $filesystem = new Filesystem();

        if (!$filesystem->isDirectory($path)) {
            $output->writeln("<info>No compiled views found.</info>");
            return Command::SUCCESS;
        }

        foreach ($filesystem->files($path) as $file) {
            $filesystem->delete($file);
        }

        $output->writeln("<info>Compiled views cleared!</info>");

        return Command::SUCCESS;
    }
}

