<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StorageLinkCommand extends Command
{
    protected function configure()
    {
        $this->setName('storage:link')
            ->setDescription('Create a symbolic link from "storage/app/public" to "public/storage"');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $target = __DIR__ . '/../../../../../../../storage/app/public';
        $link = __DIR__ . '/../../../../../../../public/storage';

        if (file_exists($link)) {
            $output->writeln("<error>The \"public/storage\" directory already exists.</error>");
            return Command::FAILURE;
        }

        if (!is_dir($target)) {
            mkdir($target, 0755, true);
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $command = "mklink /D \"{$link}\" \"{$target}\"";
            exec($command, $result, $code);
        } else {
            $code = symlink($target, $link) ? 0 : 1;
        }

        if ($code === 0) {
            $output->writeln("<info>The [public/storage] link has been connected to [storage/app/public].</info>");
            return Command::SUCCESS;
        }

        $output->writeln("<error>Failed to create symbolic link.</error>");
        return Command::FAILURE;
    }
}

