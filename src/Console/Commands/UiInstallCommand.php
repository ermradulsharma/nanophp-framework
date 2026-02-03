<?php

namespace Nano\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class UiInstallCommand extends Command
{
    protected function configure()
    {
        $this->setName('ui:install')
            ->setDescription('Install a frontend preset (react, vue, angular)')
            ->addArgument('preset', InputArgument::REQUIRED, 'The preset name (react, vue, angular)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $preset = strtolower($input->getArgument('preset'));
        $validPresets = ['react', 'vue', 'angular'];

        if (!in_array($preset, $validPresets)) {
            $output->writeln("<error>Invalid preset. Supported: react, vue, angular</error>");
            return Command::FAILURE;
        }

        $output->writeln("<info>Installing {$preset} preset...</info>");

        switch ($preset) {
            case 'react':
                return $this->installReact($output, $input);
            case 'vue':
                return $this->installVue($output, $input);
            case 'angular':
                return $this->installAngular($output, $input);
        }

        return Command::SUCCESS;
    }

    protected function installReact(OutputInterface $output, InputInterface $input): int
    {
        $output->writeln("<comment>Installing React packages...</comment>");
        exec('npm install --save-dev @vitejs/plugin-react react react-dom');

        $this->updateViteConfig('react');
        $this->scaffoldReact();

        $output->writeln("<info>React preset installed successfully!</info>");
        $output->writeln("<comment>Run 'npm run dev' to start.</comment>");

        return Command::SUCCESS;
    }

    protected function installVue(OutputInterface $output, InputInterface $input): int
    {
        $output->writeln("<comment>Installing Vue packages...</comment>");
        exec('npm install --save-dev @vitejs/plugin-vue vue');

        $this->updateViteConfig('vue');
        $this->scaffoldVue();

        $output->writeln("<info>Vue preset installed successfully!</info>");
        $output->writeln("<comment>Run 'npm run dev' to start.</comment>");

        return Command::SUCCESS;
    }

    protected function installAngular(OutputInterface $output, InputInterface $input): int
    {
        $output->writeln("<error>Angular preset is coming soon in NanoPHP!</error>");
        return Command::FAILURE;
    }

    protected function updateViteConfig(string $preset)
    {
        $path = 'vite.config.js';
        $content = file_get_contents($path);

        if ($preset === 'react' && !str_contains($content, 'plugin-react')) {
            $import = "import react from '@vitejs/plugin-react';\n";
            $content = $import . $content;
            $content = str_replace('plugins: [', "plugins: [\n        react(),", $content);
        } elseif ($preset === 'vue' && !str_contains($content, 'plugin-vue')) {
            $import = "import vue from '@vitejs/plugin-vue';\n";
            $content = $import . $content;
            $content = str_replace('plugins: [', "plugins: [\n        vue(),", $content);
        }

        file_put_contents($path, $content);
    }

    protected function scaffoldReact()
    {
        $dir = 'resources/js';
        if (!is_dir($dir . '/components')) {
            mkdir($dir . '/components', 0755, true);
        }

        $example = <<<EOT
import React from 'react';
import ReactDOM from 'react-dom/client';

function App() {
    return (
        <div className="react-hero">
            <h1>Hello from NanoPHP + React!</h1>
            <p>Full-stack power unlocked.</p>
        </div>
    );
}

if (document.getElementById('app')) {
    const root = ReactDOM.createRoot(document.getElementById('app'));
    root.render(<App />);
}
EOT;
        file_put_contents($dir . '/app.jsx', $example);
        // Rename app.js to app.js (keeping entry point flexible) or add to vite config
    }

    protected function scaffoldVue()
    {
        $dir = 'resources/js';
        if (!is_dir($dir . '/components')) {
            mkdir($dir . '/components', 0755, true);
        }

        $example = <<<EOT
import { createApp } from 'vue';
import App from './components/App.vue';

const app = createApp(App);
app.mount('#app');
EOT;
        file_put_contents($dir . '/app.js', $example);

        $appVue = <<<EOT
<template>
  <div class="vue-hero">
    <h1>Hello from NanoPHP + Vue!</h1>
    <p>Modern UI components powered by NanoPHP.</p>
  </div>
</template>

<script>
export default {
  name: 'App'
}
</script>
EOT;
        file_put_contents($dir . '/components/App.vue', $appVue);
    }
}

