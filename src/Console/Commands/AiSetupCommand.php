<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class AiSetupCommand extends Command
{
    protected function configure()
    {
        $this->setName('ai:setup')
            ->setDescription('Configure the AI brain for NanoPHP');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            "<info>NanoAI Brain Setup</info>",
            "<comment>==================</comment>",
            "To use AI power, you need a Gemini API Key.",
            "Get one here: <href=https://aistudio.google.com/app/apikey>https://aistudio.google.com/app/apikey</>",
            "",
        ]);

        $helper = $this->getHelper('question');
        $question = new Question('Enter your Gemini API Key: ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        $key = $helper->ask($input, $output, $question);

        if (empty($key)) {
            $output->writeln("<error>Key cannot be empty!</error>");
            return Command::FAILURE;
        }

        if ($this->setKeyInEnvironmentFile($key)) {
            $output->writeln("");
            $output->writeln("<info>AI Brain activated successfully! 🧠✨</info>");
            return Command::SUCCESS;
        }

        $output->writeln("<error>Failed to update .env file.</error>");
        return Command::FAILURE;
    }

    protected function setKeyInEnvironmentFile(string $key): bool
    {
        $path = __DIR__ . '/../../../../../../../.env';
        if (!file_exists($path)) {
            return false;
        }

        $content = file_get_contents($path);

        if (str_contains($content, 'AI_KEY=')) {
            $content = preg_replace('/AI_KEY=.*/', "AI_KEY={$key}", $content);
        } else {
            $content .= "\nAI_KEY={$key}";
        }

        return file_put_contents($path, $content) !== false;
    }
}

