<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeMailCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:mail')
            ->setDescription('Create a new email class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the mailable');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = ucfirst($input->getArgument('name'));
        $path = __DIR__ . '/../../../Mail/' . $name . '.php';

        if (file_exists($path)) {
            $output->writeln("<error>Mailable already exists!</error>");
            return Command::FAILURE;
        }

        $stub = <<<EOT
<?php

namespace Nano\Framework\Mail;

use Nano\Framework\Mailable;

class {$name} extends Mailable
{
    /**
     * Build the message.
     */
    public function build()
    {
        return \$this->view('emails.default')
                    ->subject('New Message');
    }
}
EOT;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $stub);
        $output->writeln("<info>Mailable created successfully: {$name}</info>");

        return Command::SUCCESS;
    }
}

