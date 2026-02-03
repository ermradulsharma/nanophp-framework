<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Database\Capsule\Manager as Capsule;

class MigrateFreshCommand extends Command
{
    protected function configure()
    {
        $this->setName('migrate:fresh')
            ->setDescription('Drop all tables and re-run all migrations');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $driver = $_ENV['DB_DRIVER'] ?? 'mysql';

        if ($driver === 'sqlite') {
            $output->writeln("<comment>Wiping SQLite database...</comment>");
            $this->wipeSqliteDatabase();
        } else {
            $output->writeln("<comment>Dropping all tables...</comment>");
            $this->dropAllTables($output);
        }

        $output->writeln("<info>Database cleared successfully.</info>");

        // Call migrate command
        $output->writeln("<comment>Running migrations...</comment>");

        return $this->getApplication()->find('migrate')->run($input, $output);
    }

    protected function wipeSqliteDatabase()
    {
        $database = $_ENV['DB_DATABASE'] ?? 'database';
        $path = __DIR__ . '/../../../../../../../database/' . $database . '.sqlite';

        if (file_exists($path)) {
            unlink($path);
        }
        touch($path);
    }

    protected function dropAllTables(OutputInterface $output)
    {
        $driver = $_ENV['DB_DRIVER'] ?? 'mysql';

        if ($driver === 'pgsql') {
            Capsule::statement("SET session_replication_role = 'replica'");
            $tables = Capsule::select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public'");
            foreach ($tables as $table) {
                $output->writeln("<info>Dropping table: {$table->tablename}</info>");
                Capsule::schema()->dropIfExists($table->tablename);
            }
            Capsule::statement("SET session_replication_role = 'origin'");
        } else {
            Capsule::statement('SET FOREIGN_KEY_CHECKS = 0');
            $tables = Capsule::getPdo()->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($tables as $tableName) {
                $output->writeln("<info>Dropping table: {$tableName}</info>");
                Capsule::schema()->dropIfExists($tableName);
            }
            Capsule::statement('SET FOREIGN_KEY_CHECKS = 1');
        }
    }
}

