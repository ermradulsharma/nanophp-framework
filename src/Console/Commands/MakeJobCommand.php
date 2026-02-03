<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeJobCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:job')
            ->setDescription('Create a new job class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the job');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = ucfirst($input->getArgument('name'));
        $path = __DIR__ . '/../../../Jobs/' . $name . '.php';

        if (file_exists($path)) {
            $output->writeln("<error>Job already exists!</error>");
            return Command::FAILURE;
        }

        $stub = <<<EOT
<?php

namespace Nano\Framework\Jobs;

use Nano\Framework\Queue\Job;
use Nano\Framework\Queue\ShouldQueue;

class {$name} extends Job implements ShouldQueue
{
    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
EOT;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $stub);
        $output->writeln("<info>Job created successfully: {$name}</info>");

        return Command::SUCCESS;
    }
}
