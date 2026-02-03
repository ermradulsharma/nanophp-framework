<?php

namespace Nano\Framework;

use DI\Container;
use DI\ContainerBuilder;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Dotenv\Dotenv;
use Nano\Framework\Middleware\MiddlewareStack;
use Nano\Framework\Http\Request;
use Nano\Framework\Auth\Gate;
use Nano\Framework\Auth;
use function FastRoute\simpleDispatcher;

class Application
{
    private Container $container;
    private $dispatcher;
    protected static $instance;

    // Define Global Middleware here
    private array $globalMiddleware = [
        \Nano\Framework\Middleware\SecurityHeadersMiddleware::class,
        \Nano\Framework\Middleware\TrimStrings::class,
        \Nano\Framework\Middleware\CsrfMiddleware::class,
    ];

    private string $basePath;

    public function __construct(Container $container, string $basePath = null)
    {
        $this->container = $container;
        $this->basePath = $basePath ?? dirname(__DIR__, 4); // Default to 4 levels up from vendor/nanophp/framework/src
        static::$instance = $this;
        $GLOBALS['__nano_app_instance'] = $this;

        // Ensure Application is in the container
        if (!$this->container->has(Application::class)) {
            $this->container->set(Application::class, $this);
        }

        $this->buildDispatcher();
    }

    /**
     * Get all core framework console commands.
     */
    public function getCoreCommands(): array
    {
        return [
            new \Nano\Framework\Console\Commands\ServeCommand(),
            new \Nano\Framework\Console\Commands\MakeControllerCommand(),
            new \Nano\Framework\Console\Commands\MakeModelCommand(),
            new \Nano\Framework\Console\Commands\RouteListCommand(),
            new \Nano\Framework\Console\Commands\MakeMiddlewareCommand(),
            new \Nano\Framework\Console\Commands\MakeCommandCommand(),
            new \Nano\Framework\Console\Commands\MakeMigrationCommand(),
            new \Nano\Framework\Console\Commands\MigrateCommand(),
            new \Nano\Framework\Console\Commands\MigrateRollbackCommand(),
            new \Nano\Framework\Console\Commands\MakeSeederCommand(),
            new \Nano\Framework\Console\Commands\DbSeedCommand(),
            new \Nano\Framework\Console\Commands\UiInstallCommand(),
            new \Nano\Framework\Console\Commands\MakeFacadeCommand(),
            new \Nano\Framework\Console\Commands\MakeRequestCommand(),
            new \Nano\Framework\Console\Commands\MakeServiceCommand(),
            new \Nano\Framework\Console\Commands\MakeFactoryCommand(),
            new \Nano\Framework\Console\Commands\TinkerCommand(),
            new \Nano\Framework\Console\Commands\KeyGenerateCommand(),
            new \Nano\Framework\Console\Commands\MigrateFreshCommand(),
            new \Nano\Framework\Console\Commands\ViewClearCommand(),
            new \Nano\Framework\Console\Commands\MakeProviderCommand(),
            new \Nano\Framework\Console\Commands\StorageLinkCommand(),
            new \Nano\Framework\Console\Commands\MakeTestCommand(),
            new \Nano\Framework\Console\Commands\MakeEventCommand(),
            new \Nano\Framework\Console\Commands\MakeListenerCommand(),
            new \Nano\Framework\Console\Commands\MakePolicyCommand(),
            new \Nano\Framework\Console\Commands\MakeMailCommand(),
            new \Nano\Framework\Console\Commands\DownCommand(),
            new \Nano\Framework\Console\Commands\UpCommand(),
            new \Nano\Framework\Console\Commands\AiSetupCommand(),
            new \Nano\Framework\Console\Commands\AiGenerateCommand(),
            new \Nano\Framework\Console\Commands\AiFixCommand(),
            new \Nano\Framework\Console\Commands\MakeResourceCommand(),
            new \Nano\Framework\Console\Commands\MakeExceptionCommand(),
            new \Nano\Framework\Console\Commands\MakeJobCommand(),
            new \Nano\Framework\Console\Commands\AboutCommand(),
            new \Nano\Framework\Console\Commands\MakeTraitCommand(),
            new \Nano\Framework\Console\Commands\MakeInterfaceCommand(),
            new \Nano\Framework\Console\Commands\MakeComponentCommand(),
            new \Nano\Framework\Console\Commands\MakeNotificationCommand(),
            new \Nano\Framework\Console\Commands\MakeSmsCommand(),
            new \Nano\Framework\Console\Commands\ConfigCacheCommand(),
            new \Nano\Framework\Console\Commands\ConfigClearCommand(),
            new \Nano\Framework\Console\Commands\CacheClearCommand(),
            new \Nano\Framework\Console\Commands\LogClearCommand(),
            new \Nano\Framework\Console\Commands\AiLogsCommand(),
            new \Nano\Framework\Console\Commands\MakeAuthCommand(),
            new \Nano\Framework\Console\Commands\MakeCrudCommand(),
            new \Nano\Framework\Console\Commands\QueueWorkCommand(),
            new \Nano\Framework\Console\Commands\QueueFailedCommand(),
            new \Nano\Framework\Console\Commands\QueueRetryCommand(),
            new \Nano\Framework\Console\Commands\QueueFlushCommand(),
            new \Nano\Framework\Console\Commands\QueueTableCommand(),
            new \Nano\Framework\Console\Commands\QueueRestartCommand(),
            new \Nano\Framework\Console\Commands\ScheduleRunCommand(),
            new \Nano\Framework\Console\Commands\ScheduleListCommand(),
            new \Nano\Framework\Console\Commands\SanctumInstallCommand(),
        ];
    }

    public static function getInstance(): ?self
    {
        return static::$instance ?? ($GLOBALS['__nano_app_instance'] ?? null);
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    private function buildDispatcher(): void
    {
        $this->dispatcher = simpleDispatcher(function (RouteCollector $r) {
            $router = $this->container->get(\Nano\Framework\Router::class);

            // Require routes (they now use Facades)
            $routesPath = $this->basePath . '/routes/web.php';
            if (file_exists($routesPath)) {
                require $routesPath;
            }

            $router->registerRoutes($r);
        });
    }

    public function run(): void
    {
        try {
            $psrRequest = $this->container->get(ServerRequestInterface::class);
            $request = new Request($psrRequest);

            // Register our fluent request in the container
            $this->container->set('request', $request);
            $this->container->set(Request::class, $request);

            // Register the Gate manager
            $gate = new Gate($this->container->get(Auth::class));
            $this->container->set('gate', $gate);
            $this->container->set(Gate::class, $gate);

            // Register Storage and Cache
            $this->container->set('storage', $this->container->get('storage'));
            $this->container->set('cache', $this->container->get('cache'));

            // Set container for Facades
            \Nano\Framework\Facade::setContainer($this->container);

            // 1. Dispatch route first to get route-specific metadata (middleware)
            $routeInfo = $this->dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());

            $middlewareStack = new MiddlewareStack($this->container);

            // 2. Add Global Middleware
            foreach ($this->globalMiddleware as $middleware) {
                $middlewareStack->add($middleware);
            }

            // 3. Handle Route Info and Add Route-Specific Middleware
            $routeHandler = null;
            $vars = [];

            switch ($routeInfo[0]) {
                case Dispatcher::NOT_FOUND:
                    $routeHandler = function () {
                        return new TextResponse('404 Not Found', 404);
                    };
                    break;
                case Dispatcher::METHOD_NOT_ALLOWED:
                    $routeHandler = function () {
                        return new TextResponse('405 Method Not Allowed', 405);
                    };
                    break;
                case Dispatcher::FOUND:
                    $route = $routeInfo[1]; // This is our Nano\Framework\Route object
                    $vars = $routeInfo[2];

                    if ($route instanceof \Nano\Framework\Route) {
                        // Add route-specific middleware
                        foreach ($route->getMiddleware() as $m) {
                            $middlewareStack->add($m);
                        }
                        $routeHandler = $route->handler;
                    } else {
                        $routeHandler = $route;
                    }
                    break;
            }

            $response = $middlewareStack->process($request, function ($req) use ($routeHandler, $vars) {
                if (is_string($routeHandler) && str_contains($routeHandler, '@')) {
                    $routeHandler = explode('@', $routeHandler);
                }

                $result = null;
                if (is_array($routeHandler)) {
                    [$controllerClass, $method] = $routeHandler;
                    $controller = $this->container->get($controllerClass);
                    $result = $this->container->call([$controller, $method], $vars);
                } else {
                    $result = $this->container->call($routeHandler, $vars);
                }

                if ($result instanceof ResponseInterface) {
                    return $result;
                }

                if (is_array($result) || is_object($result)) {
                    return new JsonResponse($result);
                }

                return new HtmlResponse((string)$result);
            });

            (new SapiEmitter())->emit($response);
        } catch (\Throwable $e) {
            $emitter = new SapiEmitter();
            $response = new JsonResponse([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
            $emitter->emit($response);
        }
    }
}
