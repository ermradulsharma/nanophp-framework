<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeProviderCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:provider')
            ->setDescription('Create a new service provider class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the provider');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = ucfirst($input->getArgument('name'));
        if (!str_ends_with($name, 'ServiceProvider')) {
            $name .= 'ServiceProvider';
        }

        $path = __DIR__ . '/../../../Providers/' . $name . '.php';

        if (file_exists($path)) {
            $output->writeln("<error>Provider already exists!</error>");
            return Command::FAILURE;
        }

        $stub = <<<EOT
<?php

namespace Nano\Framework\Providers;

class {$name}
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
EOT;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $stub);
        $output->writeln("<info>Provider created successfully: {$name}</info>");

        return Command::SUCCESS;
    }
}

