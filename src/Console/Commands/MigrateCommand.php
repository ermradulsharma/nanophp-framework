<?php

namespace Nano\Framework\Console\Commands;

use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends Command
{
    protected static $defaultName = 'migrate';

    protected function configure()
    {
        $this->setName('migrate')
            ->setDescription('Run the database migrations');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->prepareDatabase();

        $migrationsPath = __DIR__ . '/../../../../../database/migrations';
        $files = glob($migrationsPath . '/*.php');

        $ranMigrations = Capsule::table('migrations')->pluck('migration')->toArray();
        $batch = Capsule::table('migrations')->max('batch') + 1;

        $count = 0;
        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (!in_array($name, $ranMigrations)) {
                $output->writeln("<comment>Migrating: {$name}</comment>");

                $migrationResult = require $file;

                if (is_object($migrationResult)) {
                    $migration = $migrationResult;
                } else {
                    // Try to resolve class name from filename
                    // Filename format: YYYY_MM_DD_His_name_of_migration.php
                    // We need to strip the timestamp (18 chars) and convert to StudlyCase
                    $fileNameWithoutExtension = basename($file, '.php');
                    $parts = explode('_', $fileNameWithoutExtension);

                    // Remove timestamp parts (first 4 parts: Y, m, d, His/batch?)
                    // NanoPHP seems to use Y_m_d_His or similar.
                    // Let's rely on standard Laravel convention: remove date parts until we find words.
                    // Or simpler: just remove the first N characters if they are digits.

                    $className = '';
                    foreach ($parts as $part) {
                        if (!is_numeric($part)) {
                            $className .= ucfirst($part);
                        }
                    }

                    if (class_exists($className)) {
                        $migration = new $className();
                    } else {
                        // Fallback: maybe just the filename converted?
                        $output->writeln("<error>Could not resolve class for migration {$name}</error>");
                        continue;
                    }
                }

                $db = new \Nano\Framework\Database();

                try {
                    if (method_exists($migration, 'up')) {
                        $migration->up($db);
                    }

                    Capsule::table('migrations')->insert([
                        'migration' => $name,
                        'batch' => $batch
                    ]);

                    $output->writeln("<info>Migrated:  {$name}</info>");
                    $count++;
                } catch (\Throwable $e) {
                    $output->writeln("<error>Migration failed: {$name}</error>");
                    $output->writeln("<error>" . $e->getMessage() . "</error>");
                    return Command::FAILURE;
                }
            }
        }

        if ($count === 0) {
            $output->writeln("<info>Nothing to migrate.</info>");
        }

        return Command::SUCCESS;
    }

    protected function prepareDatabase()
    {
        if (!Capsule::schema()->hasTable('migrations')) {
            Capsule::schema()->create('migrations', function ($table) {
                $table->increments('id');
                $table->string('migration');
                $table->integer('batch');
            });
        }
    }
}
