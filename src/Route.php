<?php

namespace Nano\Framework;

class Route
{
    public string $method;
    public string $uri;
    public $handler;
    public ?string $name = null;
    protected array $middleware = [];

    public function __construct(string $method, string $uri, $handler)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->handler = $handler;
    }

    public function name(string $name): self
    {
        $this->name = $name;
        Router::addNamedRoute($name, $this);
        return $this;
    }

    public function middleware(array|string $middleware): self
    {
        if (is_string($middleware)) {
            $middleware = [$middleware];
        }
        $this->middleware = array_merge($this->middleware, $middleware);
        return $this;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}

