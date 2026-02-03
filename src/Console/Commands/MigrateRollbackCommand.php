<?php

namespace Nano\Framework\Console\Commands;

use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateRollbackCommand extends Command
{
    protected static $defaultName = 'migrate:rollback';

    protected function configure()
    {
        $this->setName('migrate:rollback')
            ->setDescription('Rollback the last database migration batch');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!Capsule::schema()->hasTable('migrations')) {
            $output->writeln("<info>Nothing to rollback.</info>");
            return Command::SUCCESS;
        }

        $lastBatch = Capsule::table('migrations')->max('batch');
        $migrations = Capsule::table('migrations')
            ->where('batch', $lastBatch)
            ->orderBy('id', 'desc')
            ->get();

        if ($migrations->isEmpty()) {
            $output->writeln("<info>Nothing to rollback.</info>");
            return Command::SUCCESS;
        }

        $migrationsPath = __DIR__ . '/../../../../../../../database/migrations';

        foreach ($migrations as $row) {
            $output->writeln("<comment>Rolling back: {$row->migration}</comment>");

            $file = "{$migrationsPath}/{$row->migration}.php";
            if (file_exists($file)) {
                $migration = require $file;
                $migration->down();
            }

            Capsule::table('migrations')->where('id', $row->id)->delete();
            $output->writeln("<info>Rolled back:  {$row->migration}</info>");
        }

        return Command::SUCCESS;
    }
}

