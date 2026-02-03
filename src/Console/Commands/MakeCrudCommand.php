<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

class MakeCrudCommand extends Command
{
    protected function configure()
    {
        $this->setName('make:crud')
            ->setDescription('Scaffold a full CRUD feature')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the entity (e.g. Post)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = ucfirst($input->getArgument('name'));
        $plural = $name . 's'; // Simple pluralization
        $viewFolder = strtolower($plural);

        $output->writeln("<info>Building CRUD for {$name}...</info>");

        // 1. Generate Model, Migration, Controller
        // We can reuse MakeModelCommand if we want, but let's be explicit for better control
        $this->runCommand('make:model', ['name' => $name, '-m' => true, '-c' => true], $output);

        // 2. Build CRUD Views
        $this->createViews($viewFolder, $name, $output);

        $output->writeln("");
        $output->writeln("<info>✨ CRUD for {$name} scaffolded successfully!</info>");
        $output->writeln("<comment>Don't forget to run 'php artisan migrate' and register routes.</comment>");

        return Command::SUCCESS;
    }

    protected function runCommand(string $commandName, array $args, OutputInterface $output)
    {
        $command = $this->getApplication()->find($commandName);
        $input = new ArrayInput($args);
        $command->run($input, $output);
    }

    protected function createViews(string $folder, string $name, OutputInterface $output)
    {
        $baseDir = __DIR__ . '/../../../../../../../resources/views/' . $folder;
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0755, true);
        }

        $views = ['index', 'create', 'edit', 'show'];
        foreach ($views as $view) {
            $path = "{$baseDir}/{$view}.nano.php";
            $content = "<!-- {$name} {$view} View -->\n";
            $content .= "@extends('layouts.app')\n\n";
            $content .= "@section('content')\n";
            $content .= "    <div class='container'>\n";
            $content .= "        <h1>" . ucfirst($view) . " {$name}</h1>\n";
            $content .= "        <!-- CRUD Content goes here -->\n";
            $content .= "    </div>\n";
            $content .= "@endsection";

            file_put_contents($path, $content);
            $output->writeln("<info>Created view: resources/views/{$folder}/{$view}.nano.php</info>");
        }
    }
}

