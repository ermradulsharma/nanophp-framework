<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeEventCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:event')
            ->setDescription('Create a new event class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the event');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = ucfirst($input->getArgument('name'));
        $path = __DIR__ . '/../../../Events/' . $name . '.php';

        if (file_exists($path)) {
            $output->writeln("<error>Event already exists!</error>");
            return Command::FAILURE;
        }

        $stub = <<<EOT
<?php

namespace Nano\Framework\Events;

class {$name}
{
    /**
     * Create a new event instance.
     */
    public function __construct()
    {
        //
    }
}
EOT;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $stub);
        $output->writeln("<info>Event created successfully: {$name}</info>");

        return Command::SUCCESS;
    }
}

