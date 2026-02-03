<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbSeedCommand extends Command
{
    protected function configure()
    {
        $this->setName('db:seed')
            ->setDescription('Seed the database with records')
            ->addArgument('class', InputArgument::OPTIONAL, 'The class name of the seeder', 'DatabaseSeeder');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $class = ucfirst($input->getArgument('class'));

        // Load all seeders in the directory so dependencies are resolved
        $seedersDir = __DIR__ . '/../../../../../../../database/seeders/';
        if (is_dir($seedersDir)) {
            foreach (glob($seedersDir . "*.php") as $filename) {
                require_once $filename;
            }
        }

        $className = "\\Database\\Seeders\\" . $class;
        if (!class_exists($className)) {
            $output->writeln("<error>Seeder class {$class} not found.</error>");
            return Command::FAILURE;
        }

        $seeder = new $className();

        $output->writeln("<comment>Seeding: {$class}</comment>");
        $seeder->run();
        $output->writeln("<info>Database seeding completed successfully.</info>");

        return Command::SUCCESS;
    }
}

