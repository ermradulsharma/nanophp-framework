<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class KeyGenerateCommand extends Command
{
    protected function configure()
    {
        $this->setName('key:generate')
            ->setDescription('Set the application key');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $key = $this->generateRandomKey();

        if (!$this->setKeyInEnvironmentFile($key)) {
            $output->writeln("<error>Failed to set APP_KEY in .env file</error>");
            return Command::FAILURE;
        }

        $output->writeln("<info>Application key set successfully.</info>");
        $output->writeln("<comment>Key: base64:" . base64_encode($key) . "</comment>");

        return Command::SUCCESS;
    }

    protected function generateRandomKey(): string
    {
        return random_bytes(32);
    }

    protected function setKeyInEnvironmentFile(string $key): bool
    {
        $path = base_path('.env');
        if (!file_exists($path)) {
            return false;
        }

        $content = file_get_contents($path);
        $encodedKey = 'base64:' . base64_encode($key);

        if (str_contains($content, 'APP_KEY=')) {
            $content = preg_replace('/APP_KEY=.*/', "APP_KEY={$encodedKey}", $content);
        } else {
            $content .= "\nAPP_KEY={$encodedKey}";
        }

        return file_put_contents($path, $content) !== false;
    }
}
