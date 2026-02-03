<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeFacadeCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:facade')
            ->setDescription('Create a new facade class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the facade');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = ucfirst($input->getArgument('name'));
        $path = __DIR__ . '/../../../Facades/' . $name . '.php';

        if (file_exists($path)) {
            $output->writeln("<error>Facade already exists!</error>");
            return Command::FAILURE;
        }

        $stub = <<<EOT
<?php

namespace Nano\Framework\Facades;

use Nano\Framework\Facade;

class {$name} extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'facade-accessor-here';
    }
}
EOT;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $stub);
        $output->writeln("<info>Facade created successfully: {$name}</info>");

        return Command::SUCCESS;
    }
}

