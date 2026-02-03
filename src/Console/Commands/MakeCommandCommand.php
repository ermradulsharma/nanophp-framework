<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeCommandCommand extends Command
{
    protected static $defaultName = 'make:command';

    protected function configure()
    {
        $this->setName('make:command')
            ->setDescription('Create a new Artisan command')
            ->addArgument('name', InputArgument::REQUIRED, 'The class name of the command');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $className = ucfirst($name);

        $dir = __DIR__ . '/';
        $path = $dir . $className . '.php';

        if (file_exists($path)) {
            $output->writeln("<error>Command already exists!</error>");
            return Command::FAILURE;
        }

        $commandKey = strtolower(preg_replace('/(?<!^)[A-Z]/', ':$0', str_replace('Command', '', $className)));

        $stub = <<<EOT
<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class {$className} extends Command
{
    protected static \$defaultName = '{$commandKey}';

    protected function configure()
    {
        \$this->setDescription('Command description here');
    }

    protected function execute(InputInterface \$input, OutputInterface \$output): int
    {
        \$output->writeln('Hello from {$className}!');
        return Command::SUCCESS;
    }
}
EOT;

        file_put_contents($path, $stub);

        $output->writeln("<info>Command [src/Core/Console/Commands/{$className}.php] created successfully.</info>");
        $output->writeln("<comment>Remember to register it in 'artisan' script or a service provider.</comment>");

        return Command::SUCCESS;
    }
}

