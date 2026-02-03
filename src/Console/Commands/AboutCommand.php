<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class AboutCommand extends Command
{
    protected function configure()
    {
        $this->setName('about')
            ->setDescription('Display basic information about your application');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("");
        $output->writeln("  <info>NanoPHP Framework</info> <comment>1.1.0 (Stable)</comment>");
        $output->writeln("");

        $table = new Table($output);
        $table->setHeaders(['Category', 'Details']);
        $table->addRows([
            ['Application Name', $_ENV['APP_NAME'] ?? 'NanoPHP'],
            ['Environment', defined('PHP_SAPI') ? PHP_SAPI : 'unknown'],
            ['Debug Mode', ($_ENV['APP_DEBUG'] ?? 'false') === 'true' ? '<info>ENABLED</info>' : '<comment>DISABLED</comment>'],
            ['PHP Version', PHP_VERSION],
            ['OS', PHP_OS_FAMILY],
            ['------------------', '------------------'],
            ['Database Driver', $_ENV['DB_DRIVER'] ?? 'none'],
            ['Database Name', $_ENV['DB_DATABASE'] ?? 'none'],
            ['Host', $_ENV['DB_HOST'] ?? 'none'],
        ]);
        $table->render();

        return Command::SUCCESS;
    }
}

