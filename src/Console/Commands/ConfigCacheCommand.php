<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigCacheCommand extends Command
{
    protected function configure()
    {
        $this->setName('config:cache')
            ->setDescription('Create a cache file for faster configuration loading');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configDir = __DIR__ . '/../../../../../../../config';
        $cachePath = __DIR__ . '/../../../../../../../storage/framework/cache/config.php';

        if (!is_dir(dirname($cachePath))) {
            mkdir(dirname($cachePath), 0755, true);
        }

        $config = [];

        // Scan config directory
        foreach (glob($configDir . '/*.php') as $file) {
            $key = basename($file, '.php');
            // We don't want to cache 'definitions.php' as it might contain closures/logical code 
            // that doesn't serialize well. But Laravel caches simple arrays.
            if ($key !== 'definitions') {
                $config[$key] = require $file;
            }
        }

        // Also cache .env values for maximum speed
        $config['env'] = $_ENV;

        $export = '<?php return ' . var_export($config, true) . ';';
        file_put_contents($cachePath, $export);

        $output->writeln("<info>Configuration cached successfully!</info>");
        $output->writeln("<comment>Cache location: storage/framework/cache/config.php</comment>");

        return Command::SUCCESS;
    }
}

