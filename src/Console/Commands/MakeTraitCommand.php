<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeTraitCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:trait')
            ->setDescription('Create a new PHP trait')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the trait');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = ucfirst($input->getArgument('name'));
        $path = __DIR__ . '/../../../Traits/' . $name . '.php';

        if (file_exists($path)) {
            $output->writeln("<error>Trait already exists!</error>");
            return Command::FAILURE;
        }

        $stub = <<<EOT
<?php

namespace Nano\Framework\Traits;

trait {$name}
{
    /**
     * Reusable method example.
     */
    public function exampleMethod()
    {
        //
    }
}
EOT;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $stub);
        $output->writeln("<info>Trait created successfully: App\Traits\\{$name}</info>");

        return Command::SUCCESS;
    }
}

