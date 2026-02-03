<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeNotificationCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:notification')
            ->setDescription('Create a new notification class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the notification');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = ucfirst($input->getArgument('name'));
        $path = __DIR__ . '/../../../Notifications/' . $name . '.php';

        if (file_exists($path)) {
            $output->writeln("<error>Notification already exists!</error>");
            return Command::FAILURE;
        }

        $stub = <<<EOT
<?php

namespace Nano\Framework\Notifications;

class {$name}
{
    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(mixed \$notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(mixed \$notifiable)
    {
        // return (new Mailable)->to(\$notifiable->email);
    }

    /**
     * Get the array representation of the notification (for database/api).
     */
    public function toArray(mixed \$notifiable): array
    {
        return [
            //
        ];
    }
}
EOT;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $stub);
        $output->writeln("<info>Notification created successfully: App\Notifications\\{$name}</info>");

        return Command::SUCCESS;
    }
}

