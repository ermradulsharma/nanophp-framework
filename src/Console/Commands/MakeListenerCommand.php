<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeListenerCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:listener')
            ->setDescription('Create a new event listener class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the listener')
            ->addOption('event', 'e', InputOption::VALUE_OPTIONAL, 'The event class that the listener handles');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = ucfirst($input->getArgument('name'));
        $event = $input->getOption('event');

        $path = __DIR__ . '/../../../Listeners/' . $name . '.php';

        if (file_exists($path)) {
            $output->writeln("<error>Listener already exists!</error>");
            return Command::FAILURE;
        }

        $eventImport = $event ? "use Nano\Framework\Events\\{$event};" : "";
        $typeHint = $event ? "{$event} \$event" : "\$event";

        $stub = <<<EOT
<?php

namespace Nano\Framework\Listeners;

{$eventImport}

class {$name}
{
    /**
     * Handle the event.
     */
    public function handle({$typeHint}): void
    {
        //
    }
}
EOT;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $stub);
        $output->writeln("<info>Listener created successfully: {$name}</info>");

        return Command::SUCCESS;
    }
}

