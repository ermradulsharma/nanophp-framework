<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeComponentCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:component')
            ->setDescription('Create a new Blade component')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the component');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = ucfirst($input->getArgument('name'));
        $viewName = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $name));

        $classPath = __DIR__ . '/../../../View/Components/' . $name . '.php';
        $viewPath = __DIR__ . '/../../../../../../../resources/views/components/' . $viewName . '.nano.php';

        if (file_exists($classPath)) {
            $output->writeln("<error>Component class already exists!</error>");
            return Command::FAILURE;
        }

        $classStub = <<<EOT
<?php

namespace Nano\Framework\View\Components;

use Nano\Framework\ViewComponent;

class {$name} extends ViewComponent
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.{$viewName}');
    }
}
EOT;

        $viewStub = "<div>\n    <!-- Simplicity is the ultimate sophistication. - Leonardo da Vinci -->\n</div>";

        if (!is_dir(dirname($classPath))) mkdir(dirname($classPath), 0755, true);
        if (!is_dir(dirname($viewPath))) mkdir(dirname($viewPath), 0755, true);

        file_put_contents($classPath, $classStub);
        file_put_contents($viewPath, $viewStub);

        $output->writeln("<info>Component created successfully!</info>");
        $output->writeln("<comment>Class: App\View\Components\\{$name}</comment>");
        $output->writeln("<comment>View: resources/views/components/{$viewName}.nano.php</comment>");

        return Command::SUCCESS;
    }
}

