<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeMiddlewareCommand extends Command
{
    protected static $defaultName = 'make:middleware';

    protected function configure()
    {
        $this->setName('make:middleware')
            ->setDescription('Create a new middleware class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the middleware');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $className = ucfirst($name);

        $dir = __DIR__ . '/../../../Middleware';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $path = $dir . '/' . $className . '.php';

        if (file_exists($path)) {
            $output->writeln("<error>Middleware already exists!</error>");
            return Command::FAILURE;
        }

        $stub = <<<EOT
<?php

namespace Nano\Framework\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class {$className}
{
    public function process(ServerRequestInterface \$request, callable \$next): ResponseInterface
    {
        // Pre-processing logic...

        \$response = \$next(\$request);

        // Post-processing logic...

        return \$response;
    }
}
EOT;

        file_put_contents($path, $stub);

        $output->writeln("<info>Middleware [src/Core/Middleware/{$className}.php] created successfully.</info>");

        return Command::SUCCESS;
    }
}

