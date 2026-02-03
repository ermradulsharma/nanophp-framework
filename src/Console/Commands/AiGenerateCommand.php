<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Nano\Framework\AI;

class AiGenerateCommand extends Command
{
    protected function configure()
    {
        $this->setName('ai:generate')
            ->setDescription('Scaffold a feature using AI')
            ->addArgument('prompt', InputArgument::REQUIRED, 'The feature you want to build');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userPrompt = $input->getArgument('prompt');
        $output->writeln("<info>NanoAI is thinking... 🧠⏳</info>");

        $ai = new AI();

        $systemPrompt = "Act as NanoPHP Expert. Task: Generate JSON for: {$userPrompt}. Format: { \"classes\": [ { \"type\": \"model|controller|migration\", \"name\": \"...\", \"code\": \"...\" } ], \"message\": \"...\" }. Classes must be valid PHP for NanoPHP framework (App\Models, App\Controllers namespaces). Return JSON ONLY.";

        try {
            $response = $ai->ask($systemPrompt);
            $response = preg_replace('/^```json|```$/', '', trim($response));
            $data = json_decode($response, true);

            if (!$data || !isset($data['classes'])) {
                $output->writeln("<error>AI returned invalid JSON.</error>");
                return Command::FAILURE;
            }

            foreach ($data['classes'] as $class) {
                $this->saveFile($class, $output);
            }

            $output->writeln("\n<info>✨ Done! NanoAI created " . count($data['classes']) . " files.</info>");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln("<error>AI Error: " . $e->getMessage() . "</error>");
            return Command::FAILURE;
        }
    }

    protected function saveFile(array $class, OutputInterface $output)
    {
        $dirMap = [
            'model' => 'src/Models/',
            'controller' => 'src/Controllers/',
            'migration' => 'database/migrations/'
        ];

        $prefix = ($class['type'] === 'migration') ? date('Y_m_d_His') . '_' : '';
        $path = $dirMap[$class['type']] . $prefix . $class['name'] . '.php';
        $fullPath = __DIR__ . '/../../../../../../../' . $path;

        if (!is_dir(dirname($fullPath))) mkdir(dirname($fullPath), 0755, true);
        file_put_contents($fullPath, $class['code']);
        $output->writeln("<info>Created: {$path}</info>");
    }
}

