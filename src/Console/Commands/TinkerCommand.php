<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psy\Configuration;
use Psy\Shell;

class TinkerCommand extends Command
{
    protected function configure()
    {
        $this->setName('tinker')
            ->setDescription('Interact with your application');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        global $container;

        $config = new Configuration();
        $shell = new Shell($config);

        // Add variables to shell
        $shell->setScopeVariables([
            'container' => $container,
            'app' => $container->get(\Nano\Framework\Application::class),
        ]);

        $shell->run();

        return Command::SUCCESS;
    }
}

