<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueueTableCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('queue:table')
            ->setDescription('Create a migration for the queue jobs database table');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $timestamp = date('Y_m_d_His');
        $migrationPath = __DIR__ . '/../../../../database/migrations/' . $timestamp . '_create_jobs_table.php';

        $stub = <<<'EOT'
<?php

use Nano\Framework\Database;

return new class
{
    public function up(Database $db): void
    {
        $db->exec("
            CREATE TABLE IF NOT EXISTS jobs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                queue TEXT NOT NULL,
                payload TEXT NOT NULL,
                attempts INTEGER NOT NULL DEFAULT 0,
                reserved_at INTEGER,
                available_at INTEGER NOT NULL,
                created_at INTEGER NOT NULL
            )
        ");

        $db->exec("CREATE INDEX idx_jobs_queue ON jobs(queue)");
        $db->exec("CREATE INDEX idx_jobs_reserved_at ON jobs(reserved_at)");
        $db->exec("CREATE INDEX idx_jobs_available_at ON jobs(available_at)");
    }

    public function down(Database $db): void
    {
        $db->exec("DROP TABLE IF EXISTS jobs");
    }
};
EOT;

        file_put_contents($migrationPath, $stub);

        $output->writeln("<info>Migration created successfully!</info>");
        $output->writeln("Next: Run <comment>php artisan migrate</comment> to create the jobs table.");

        return Command::SUCCESS;
    }
}
