<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Nano\Framework\AI;

class AiLogsCommand extends Command
{
    protected function configure()
    {
        $this->setName('ai:logs')
            ->setDescription('Analyze the app logs using AI and find hidden bugs');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logPath = __DIR__ . '/../../../../../../../storage/logs/nano.log';

        if (!file_exists($logPath) || empty(file_get_contents($logPath))) {
            $output->writeln("<comment>Logs are empty. No bugs found yet! 🎉</comment>");
            return Command::SUCCESS;
        }

        $output->writeln("<info>NanoAI is scanning logs for patterns... 🧠🔍</info>");

        $logs = file_get_contents($logPath);
        // Take last 2000 characters to avoid token limits
        $recentLogs = substr($logs, -2000);

        $ai = new AI();
        $prompt = "Scan these NanoPHP logs for errors or bugs. Summarize the major issues and give code-level fix suggestions: \n\n{$recentLogs}";

        try {
            $response = $ai->ask($prompt);
            $output->writeln("\n<comment>NanoAI Log Report:</comment>");
            $output->writeln($response);
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln("<error>AI Error: " . $e->getMessage() . "</error>");
            return Command::FAILURE;
        }
    }
}

