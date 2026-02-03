<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeSmsCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:sms')
            ->setDescription('Create a new SMS message class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the SMS class');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = ucfirst($input->getArgument('name'));
        if (!str_ends_with($name, 'Sms')) {
            $name .= 'Sms';
        }

        $path = __DIR__ . '/../../../Communications/Sms/' . $name . '.php';

        if (file_exists($path)) {
            $output->writeln("<error>SMS class already exists!</error>");
            return Command::FAILURE;
        }

        $stub = <<<EOT
<?php

namespace Nano\Framework\Communications\Sms;

class {$name}
{
    protected string \$to;
    protected string \$message;

    public function __construct(string \$to = '')
    {
        \$this->to = \$to;
    }

    /**
     * Set the recipient.
     */
    public function to(string \$number): self
    {
        \$this->to = \$number;
        return \$this;
    }

    /**
     * Set the message content.
     */
    public function content(string \$message): self
    {
        \$this->message = \$message;
        return \$this;
    }

    /**
     * Send the SMS via your preferred gateway (Twilio/Vonage/etc).
     */
    public function send(): bool
    {
        if (empty(\$this->to) || empty(\$this->message)) {
            return false;
        }

        // Logic to hit SMS Gateway API
        // error_log("Sending SMS to {\$this->to}: {\$this->message}");
        
        return true;
    }
}
EOT;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $stub);
        $output->writeln("<info>SMS class created successfully: App\Communications\Sms\\{$name}</info>");

        return Command::SUCCESS;
    }
}

