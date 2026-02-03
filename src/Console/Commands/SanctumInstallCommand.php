<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SanctumInstallCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('sanctum:install')
            ->setDescription('Install the Sanctum compatible components and resources');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Installing Sanctum...</info>');

        $this->createMigration($output);

        $output->writeln('<info>Sanctum installed successfully.</info>');

        return Command::SUCCESS;
    }

    protected function createMigration(OutputInterface $output)
    {
        $migrationPath = __DIR__ . '/../../../../../database/migrations';
        $timestamp = date('Y_m_d_His');
        $file = $migrationPath . '/' . $timestamp . '_create_personal_access_tokens_table.php';

        if (!is_dir($migrationPath)) {
            mkdir($migrationPath, 0755, true);
        }

        $stub = <<<'EOT'
<?php

use Nano\Framework\Database;

class CreatePersonalAccessTokensTable
{
    public function up()
    {
        $db = new Database();
        
        $sql = "CREATE TABLE IF NOT EXISTS personal_access_tokens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tokenable_type VARCHAR(255) NOT NULL,
            tokenable_id INTEGER NOT NULL,
            name VARCHAR(255) NOT NULL,
            token VARCHAR(64) NOT NULL UNIQUE,
            abilities TEXT,
            last_used_at DATETIME,
            expires_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        $db->exec($sql);
        
        // Add index for token lookups
        $db->exec("CREATE INDEX IF NOT EXISTS pat_token_index ON personal_access_tokens(token)");
        $db->exec("CREATE INDEX IF NOT EXISTS pat_tokenable_index ON personal_access_tokens(tokenable_type, tokenable_id)");
    }

    public function down()
    {
        $db = new Database();
        $db->exec("DROP TABLE IF EXISTS personal_access_tokens");
    }
}
EOT;

        file_put_contents($file, $stub);
        $output->writeln("<info>Created migration:</info> {$file}");
    }
}
