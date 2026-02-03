<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeTestCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:test')
            ->setDescription('Create a new test class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the test')
            ->addOption('unit', 'u', InputOption::VALUE_NONE, 'Create a unit test');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = ucfirst($input->getArgument('name'));
        if (!str_ends_with($name, 'Test')) {
            $name .= 'Test';
        }

        $type = $input->getOption('unit') ? 'Unit' : 'Feature';
        $path = __DIR__ . '/../../../../../tests/' . $type . '/' . $name . '.php';

        if (file_exists($path)) {
            $output->writeln("<error>Test already exists!</error>");
            return Command::FAILURE;
        }

        $namespace = "Tests\\" . $type;

        $stub = <<<EOT
<?php

namespace {$namespace};

use Tests\TestCase;

class {$name} extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_example(): void
    {
        \$this->assertTrue(true);
    }
}
EOT;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $stub);
        $output->writeln("<info>Test created successfully: tests/{$type}/{$name}.php</info>");

        return Command::SUCCESS;
    }
}
