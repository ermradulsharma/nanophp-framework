<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakePolicyCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:policy')
            ->setDescription('Create a new policy class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the policy');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = ucfirst($input->getArgument('name'));
        if (!str_ends_with($name, 'Policy')) {
            $name .= 'Policy';
        }

        $path = __DIR__ . '/../../../Policies/' . $name . '.php';

        if (file_exists($path)) {
            $output->writeln("<error>Policy already exists!</error>");
            return Command::FAILURE;
        }

        $stub = <<<EOT
<?php

namespace Nano\Framework\Policies;

use Nano\Framework\Models\User;

class {$name}
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User \$user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User \$user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User \$user, \$model): bool
    {
        return true;
    }
}
EOT;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $stub);
        $output->writeln("<info>Policy created successfully: {$name}</info>");

        return Command::SUCCESS;
    }
}

