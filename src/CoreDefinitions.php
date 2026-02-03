<?php

namespace Nano\Framework;

use Laminas\Diactoros\ServerRequestFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Validation\Factory;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Filesystem\Filesystem;
use Nano\Framework\Filesystem\StorageManager;
use Nano\Framework\Cache\CacheManager;
use Nano\Framework\Router;
use Nano\Framework\Application;
use Nano\Framework\AI;
use Nano\Framework\View;
use Nano\Framework\Auth;
use Nano\Framework\LogManager;

return [
    ServerRequestInterface::class => function (ContainerInterface $c) {
        return ServerRequestFactory::fromGlobals();
    },
    // Core Components
    Router::class => \DI\create(Router::class),
    'db' => function () {
        return \Illuminate\Database\Capsule\Manager::class;
    },
    Application::class => \DI\autowire(),

    Factory::class => function (ContainerInterface $c) {
        $filesystem = new Filesystem();
        $langPath = base_path('resources/lang');
        $loader = new FileLoader($filesystem, $langPath);
        $loader->addNamespace('lang', $langPath);
        $loader->load('en', 'validation', 'lang');
        $translator = new Translator($loader, 'en');
        return new Factory($translator);
    },

    \Illuminate\Contracts\View\Factory::class => function (ContainerInterface $c) {
        $filesystem = new Filesystem();
        $eventDispatcher = new \Illuminate\Events\Dispatcher(new \Illuminate\Container\Container());

        $viewPaths = [base_path('resources/views')];
        $compiledPath = base_path('storage/framework/views');

        $viewFileLoader = new \Illuminate\View\FileViewFinder($filesystem, $viewPaths);
        $bladeCompiler = new \Illuminate\View\Compilers\BladeCompiler($filesystem, $compiledPath);

        $engineResolver = new \Illuminate\View\Engines\EngineResolver();
        $engineResolver->register('blade', function () use ($bladeCompiler, $filesystem) {
            return new \Illuminate\View\Engines\CompilerEngine($bladeCompiler, $filesystem);
        });

        $viewFactory = new \Illuminate\View\Factory($engineResolver, $viewFileLoader, $eventDispatcher);
        $viewFactory->addExtension('nano.php', 'blade');

        return $viewFactory;
    },
    AI::class => \DI\autowire(),
    'view' => \DI\autowire(View::class),
    'auth' => \DI\autowire(Auth::class),
    'log' => \DI\autowire(LogManager::class),
    \Psr\Log\LoggerInterface::class => \DI\get('log'),
    'storage' => function () {
        $configPath = base_path('config/filesystems.php');
        return new StorageManager(file_exists($configPath) ? require $configPath : []);
    },
    'cache' => function () {
        $configPath = base_path('config/cache.php');
        return new CacheManager(file_exists($configPath) ? require $configPath : []);
    },
];
