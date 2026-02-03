<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeResourceCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:resource')
            ->setDescription('Create a new API resource')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the resource')
            ->addOption('collection', 'c', InputOption::VALUE_NONE, 'Create a resource collection');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = ucfirst($input->getArgument('name'));
        $isCollection = $input->getOption('collection');

        if ($isCollection && !str_ends_with($name, 'Collection')) {
            $name .= 'Collection';
        }

        $path = __DIR__ . '/../../../Http/Resources/' . $name . '.php';

        if (file_exists($path)) {
            $output->writeln("<error>Resource already exists!</error>");
            return Command::FAILURE;
        }

        $stub = <<<EOT
<?php

namespace Nano\Framework\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class {$name} extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(\$request): array
    {
        return [
            'id' => \$this->id,
            // 'name' => \$this->name,
            'created_at' => \$this->created_at,
            'updated_at' => \$this->updated_at,
        ];
    }
}
EOT;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $stub);
        $output->writeln("<info>Resource created successfully: {$name}</info>");

        return Command::SUCCESS;
    }
}

