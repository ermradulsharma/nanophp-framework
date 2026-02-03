<?php

namespace Nano\Framework;

use FastRoute\RouteCollector;

class Router
{
    protected array $routes = [];
    protected static array $namedRoutes = [];

    public function get(string $uri, $handler): Route
    {
        return $this->addRoute('GET', $uri, $handler);
    }

    public function post(string $uri, $handler): Route
    {
        return $this->addRoute('POST', $uri, $handler);
    }

    protected function addRoute(string $method, string $uri, $handler): Route
    {
        $route = new Route($method, $uri, $handler);
        $this->routes[] = $route;
        return $route;
    }

    public static function addNamedRoute(string $name, Route $route): void
    {
        self::$namedRoutes[$name] = $route;
    }

    public function registerRoutes(RouteCollector $r): void
    {
        foreach ($this->routes as $route) {
            $r->addRoute($route->method, $route->uri, $route);
        }
    }

    public static function getUrl(string $name, array $params = []): string
    {
        if (!isset(self::$namedRoutes[$name])) {
            throw new \RuntimeException("Route not found: {$name}");
        }

        $uri = self::$namedRoutes[$name]->uri;

        foreach ($params as $key => $value) {
            $uri = str_replace('{' . $key . '}', $value, $uri);
        }

        return $uri;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}

