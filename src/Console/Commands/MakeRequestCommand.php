<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeRequestCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:request')
            ->setDescription('Create a new form request class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the request');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = ucfirst($input->getArgument('name'));
        if (!str_ends_with($name, 'Request')) {
            $name .= 'Request';
        }

        $path = __DIR__ . '/../../../Requests/' . $name . '.php';

        if (file_exists($path)) {
            $output->writeln("<error>Request already exists!</error>");
            return Command::FAILURE;
        }

        $stub = <<<EOT
<?php

namespace Nano\Framework\Requests;

use Nano\Framework\Request;

class {$name} extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // 'name' => 'required|string|max:255',
        ];
    }
}
EOT;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $stub);
        $output->writeln("<info>Request created successfully: {$name}</info>");

        return Command::SUCCESS;
    }
}

