<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeMigrationCommand extends Command
{
    protected static $defaultName = 'make:migration';

    protected function configure()
    {
        $this->setName('make:migration')
            ->setDescription('Create a new migration file')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the migration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = strtolower($input->getArgument('name'));
        $timestamp = date('Y_m_d_His');
        $fileName = "{$timestamp}_{$name}.php";
        $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));

        $path = __DIR__ . '/../../../../../../../database/migrations/' . $fileName;
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        $stub = $this->getStub($name, $className);

        file_put_contents($path, $stub);

        $output->writeln("<info>Created Migration: {$fileName}</info>");

        return Command::SUCCESS;
    }

    protected function getStub(string $name, string $className): string
    {
        $table = 'table_name';
        if (str_starts_with($name, 'create_')) {
            $table = str_replace('create_', '', $name);
            $table = str_ends_with($table, '_table') ? substr($table, 0, -6) : $table;
        }

        return <<<EOT
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Database\Capsule\Manager::schema()->create('{$table}', function (Blueprint \$table) {
            \$table->id();
            \$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Database\Capsule\Manager::schema()->dropIfExists('{$table}');
    }
};
EOT;
    }
}

