<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeFactoryCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:factory')
            ->setDescription('Create a new model factory')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the factory');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = ucfirst($input->getArgument('name'));
        if (!str_ends_with($name, 'Factory')) {
            $name .= 'Factory';
        }

        $path = __DIR__ . '/../../../../../../../database/factories/' . $name . '.php';

        if (file_exists($path)) {
            $output->writeln("<error>Factory already exists!</error>");
            return Command::FAILURE;
        }

        $stub = <<<EOT
<?php

namespace Database\Factories;

use Faker\Factory as Faker;

class {$name}
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        \$faker = Faker::create();

        return [
            // 'name' => \$faker->name(),
            // 'email' => \$faker->unique()->safeEmail(),
        ];
    }
}
EOT;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $stub);
        $output->writeln("<info>Factory created successfully: {$name}</info>");

        return Command::SUCCESS;
    }
}

