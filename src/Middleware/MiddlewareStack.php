<?php

namespace Nano\Framework\Middleware;

use Psr\Http\Message\ResponseInterface;
use Nano\Framework\Http\Request;
use DI\Container;

class MiddlewareStack
{
    protected array $middleware = [];
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function add(string $middlewareWithParams): self
    {
        // Parse middleware: 'middleware_name' or 'middleware_name:param1,param2' (future support)
        // For now assume simple class name or alias
        $this->middleware[] = $middlewareWithParams;
        return $this;
    }

    public function process(Request $request, callable $kernel): ResponseInterface
    {
        $middleware = $this->middleware;
        $container = $this->container;

        $next = function (Request $request) use (&$middleware, $kernel, $container, &$next) {
            if (empty($middleware)) {
                return $kernel($request);
            }

            $middlewareClass = array_shift($middleware);

            if (class_exists($middlewareClass)) {
                $instance = $container->get($middlewareClass);
                if (method_exists($instance, 'process')) {
                    return $instance->process($request, $next);
                }
            }

            // Skip invalid middleware
            return $next($request);
        };

        return $next($request);
    }
}
