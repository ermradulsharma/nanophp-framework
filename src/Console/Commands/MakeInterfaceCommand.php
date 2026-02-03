<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeInterfaceCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:interface')
            ->setDescription('Create a new PHP interface')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the interface');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = ucfirst($input->getArgument('name'));
        $path = __DIR__ . '/../../../Contracts/' . $name . '.php';

        if (file_exists($path)) {
            $output->writeln("<error>Interface already exists!</error>");
            return Command::FAILURE;
        }

        $stub = <<<EOT
<?php

namespace Nano\Framework\Contracts;

interface {$name}
{
    /**
     * Interface method contract.
     */
    public function handle();
}
EOT;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $stub);
        $output->writeln("<info>Interface created successfully: App\Contracts\\{$name}</info>");

        return Command::SUCCESS;
    }
}

