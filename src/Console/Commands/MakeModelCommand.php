<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputOption;

class MakeModelCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:model')
            ->setDescription('Create a new Eloquent model class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the model')
            ->addOption('migration', 'm', InputOption::VALUE_NONE, 'Create a new migration file for the model')
            ->addOption('controller', 'c', InputOption::VALUE_NONE, 'Create a new controller for the model')
            ->addOption('factory', 'f', InputOption::VALUE_NONE, 'Create a new factory for the model')
            ->addOption('seeder', 's', InputOption::VALUE_NONE, 'Create a new seeder for the model')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Generate a migration, factory, seeder, and controller for the model');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = ucfirst($input->getArgument('name'));
        $className = $name;

        $path = __DIR__ . '/../../../../../../Models/' . $className . '.php';

        if (file_exists($path)) {
            $output->writeln("<error>Model already exists!</error>");
        } else {
            $stub = <<<EOT
<?php

namespace Nano\Framework\Models;

use Illuminate\Database\Eloquent\Model;

class {$className} extends Model
{
    //
}
EOT;
            file_put_contents($path, $stub);
            $output->writeln("<info>Model [src/Models/{$className}.php] created successfully.</info>");
        }

        // Handle Flags
        if ($input->getOption('all')) {
            $input->setOption('migration', true);
            $input->setOption('controller', true);
            $input->setOption('factory', true);
            $input->setOption('seeder', true);
        }

        if ($input->getOption('migration')) {
            $table = strtolower(\Illuminate\Support\Str::plural($name));
            $this->getApplication()->find('make:migration')->run(
                new \Symfony\Component\Console\Input\ArrayInput(['name' => "create_{$table}_table"]),
                $output
            );
        }

        if ($input->getOption('controller')) {
            $this->getApplication()->find('make:controller')->run(
                new \Symfony\Component\Console\Input\ArrayInput(['name' => "{$name}Controller"]),
                $output
            );
        }

        if ($input->getOption('factory')) {
            $this->getApplication()->find('make:factory')->run(
                new \Symfony\Component\Console\Input\ArrayInput(['name' => "{$name}Factory"]),
                $output
            );
        }

        if ($input->getOption('seeder')) {
            $this->getApplication()->find('make:seeder')->run(
                new \Symfony\Component\Console\Input\ArrayInput(['name' => "{$name}Seeder"]),
                $output
            );
        }

        return Command::SUCCESS;
    }
}

