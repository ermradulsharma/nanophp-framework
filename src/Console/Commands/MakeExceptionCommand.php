<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeExceptionCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:exception')
            ->setDescription('Create a new custom exception class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the exception');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = ucfirst($input->getArgument('name'));
        if (!str_ends_with($name, 'Exception')) {
            $name .= 'Exception';
        }

        $path = __DIR__ . '/../../../Exceptions/' . $name . '.php';

        if (file_exists($path)) {
            $output->writeln("<error>Exception already exists!</error>");
            return Command::FAILURE;
        }

        $stub = <<<EOT
<?php

namespace Nano\Framework\Exceptions;

use Exception;

class {$name} extends Exception
{
    /**
     * Report the exception.
     */
    public function report(): void
    {
        //
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(\$request)
    {
        // return response()->json(['error' => 'Custom error message'], 400);
    }
}
EOT;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $stub);
        $output->writeln("<info>Exception created successfully: {$name}</info>");

        return Command::SUCCESS;
    }
}

