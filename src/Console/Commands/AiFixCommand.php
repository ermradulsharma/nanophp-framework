<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Nano\Framework\AI;

class AiFixCommand extends Command
{
    protected function configure()
    {
        $this->setName('ai:fix')
            ->setDescription('Analyze and fix the last error using AI')
            ->addArgument('error', InputArgument::OPTIONAL, 'Paste the error message here');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $error = $input->getArgument('error');

        if (!$error) {
            $output->writeln("<comment>No error provided. Searching in logs...</comment>");
            // For now, let's ask user to paste it
            $output->writeln("<error>Please paste the error message as an argument.</error>");
            return Command::FAILURE;
        }

        $output->writeln("<info>Analyzing error with NanoAI... 🧠🔧</info>");

        $ai = new AI();
        $prompt = "Expert PHP debugging. Analyze this error in NanoPHP: '{$error}'. Provide: 1. Explanation, 2. Fix Code.";

        try {
            $response = $ai->ask($prompt);
            $output->writeln("\n<comment>Analysis & Fix:</comment>");
            $output->writeln($response);
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln("<error>AI Error: " . $e->getMessage() . "</error>");
            return Command::FAILURE;
        }
    }
}

