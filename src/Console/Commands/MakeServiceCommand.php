<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeServiceCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:service')
            ->setDescription('Create a new service class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the service');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = ucfirst($input->getArgument('name'));
        if (!str_ends_with($name, 'Service')) {
            $name .= 'Service';
        }

        $path = __DIR__ . '/../../../Services/' . $name . '.php';

        if (file_exists($path)) {
            $output->writeln("<error>Service already exists!</error>");
            return Command::FAILURE;
        }

        $stub = <<<EOT
<?php

namespace Nano\Framework\Services;

class {$name}
{
    /**
     * Handle the business logic.
     */
    public function handle()
    {
        //
    }
}
EOT;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $stub);
        $output->writeln("<info>Service created successfully: {$name}</info>");

        return Command::SUCCESS;
    }
}

