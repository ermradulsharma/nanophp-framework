<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeControllerCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:controller')
            ->setDescription('Create a new controller class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the controller');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        // Ensure standard naming (PascalCase)
        $className = ucfirst($name);

        $path = __DIR__ . '/../../../../../../Controllers/' . $className . '.php';

        if (file_exists($path)) {
            $output->writeln("<error>Controller already exists!</error>");
            return Command::FAILURE;
        }

        $stub = <<<EOT
<?php

namespace Nano\Framework\Controllers;

use Nano\Framework\Controller;

class {$className} extends Controller
{
    public function index()
    {
        //
    }
}
EOT;

        file_put_contents($path, $stub);

        $output->writeln("<info>Controller [src/Controllers/{$className}.php] created successfully.</info>");

        return Command::SUCCESS;
    }
}

