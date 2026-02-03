<?php

namespace Nano\Framework\Console\Commands;

use Nano\Framework\Router;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RouteListCommand extends Command
{
    protected static $defaultName = 'route:list';

    protected function configure()
    {
        $this->setName('route:list')
            ->setDescription('List all registered routes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        global $container; // From artisan.php
        $router = $container->get(\Nano\Framework\Router::class);
        $routes = $router->getRoutes();

        $table = new Table($output);
        $table->setHeaders(['Method', 'URI', 'Name', 'Action', 'Middleware']);

        foreach ($routes as $route) {
            $middleware = $route->getMiddleware();
            $table->addRow([
                $route->method,
                $route->uri,
                $route->getName() ?: 'N/A',
                is_string($route->handler) ? $route->handler : (is_array($route->handler) ? implode('@', $route->handler) : 'Closure'),
                implode(', ', $middleware) ?: 'None'
            ]);
        }

        $table->render();

        return Command::SUCCESS;
    }
}

