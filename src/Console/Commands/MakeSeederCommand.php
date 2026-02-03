<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeSeederCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:seeder')
            ->setDescription('Create a new seeder class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the seeder');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = ucfirst($input->getArgument('name'));
        if (!str_ends_with($name, 'Seeder')) {
            $name .= 'Seeder';
        }

        $path = __DIR__ . '/../../../../../../../database/seeders/' . $name . '.php';

        if (file_exists($path)) {
            $output->writeln("<error>Seeder already exists!</error>");
            return Command::FAILURE;
        }

        $stub = <<<EOT
<?php

namespace Database\Seeders;

use Illuminate\Database\Capsule\Manager as Capsule;

class {$name}
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Example: Capsule::table('users')->insert(['name' => 'John Doe']);
    }
}
EOT;

        file_put_contents($path, $stub);
        $output->writeln("<info>Seeder created successfully: {$name}</info>");

        return Command::SUCCESS;
    }
}

