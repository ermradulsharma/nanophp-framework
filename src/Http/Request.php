<?php

namespace Nano\Framework\Http;

use Psr\Http\Message\ServerRequestInterface;

class Request
{
    /**
     * The underlying PSR-7 request.
     *
     * @var ServerRequestInterface
     */
    protected ServerRequestInterface $request;

    /**
     * The cached/modified input data.
     *
     * @var array|null
     */
    protected ?array $input = null;

    /**
     * Create a new fluent request instance.
     *
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Get an input item from the request.
     *
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public function input(?string $key = null, mixed $default = null): mixed
    {
        $all = $this->all();

        if (is_null($key)) {
            return $all;
        }

        return $all[$key] ?? $default;
    }

    /**
     * Get all of the input and files for the request.
     *
     * @return array
     */
    public function all(): array
    {
        if ($this->input !== null) {
            return $this->input;
        }

        $queryParams = $this->request->getQueryParams();
        $parsedBody = $this->request->getParsedBody();

        return array_merge(
            is_array($queryParams) ? $queryParams : [],
            is_array($parsedBody) ? $parsedBody : []
        );
    }

    /**
     * Merge new input into the request's input.
     *
     * @param array $input
     * @return $this
     */
    public function merge(array $input): self
    {
        $this->input = array_merge($this->all(), $input);

        return $this;
    }

    /**
     * Determine if the request contains a given input item key.
     *
     * @param string|array $key
     * @return bool
     */
    public function has(string|array $key): bool
    {
        $keys = is_array($key) ? $key : func_get_args();
        $input = $this->all();

        foreach ($keys as $value) {
            if (!isset($input[$value])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the request method.
     *
     * @return string
     */
    public function method(): string
    {
        return $this->request->getMethod();
    }

    /**
     * Get the request path.
     *
     * @return string
     */
    public function path(): string
    {
        return $this->request->getUri()->getPath();
    }

    /**
     * Get a header from the request.
     *
     * @param string $key
     * @param mixed|null $default
     * @return string|null
     */
    public function header(string $key, mixed $default = null): ?string
    {
        return $this->request->getHeaderLine($key) ?: $default;
    }

    /**
     * Get the underlying PSR-7 request.
     *
     * @return ServerRequestInterface
     */
    public function getPsrRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * Dynamically call methods on the underlying PSR-7 request.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->request->$method(...$parameters);
    }
}
