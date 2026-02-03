<?php

namespace Nano\Framework\Cache;

use InvalidArgumentException;
use Closure;

class CacheManager
{
    /**
     * The application configuration.
     *
     * @var array
     */
    protected array $config;

    /**
     * The array of resolved cache stores.
     *
     * @var array
     */
    protected array $stores = [];

    /**
     * Create a new cache manager instance.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get a cache store instance.
     *
     * @param string|null $name
     * @return mixed
     */
    public function store(?string $name = null)
    {
        $name = $name ?: $this->config['default'] ?? 'file';

        if (! isset($this->stores[$name])) {
            $this->stores[$name] = $this->resolve($name);
        }

        return $this->stores[$name];
    }

    /**
     * Resolve the given store.
     *
     * @param string $name
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve(string $name)
    {
        $config = $this->config['stores'][$name] ?? null;

        if (is_null($config)) {
            throw new InvalidArgumentException("Cache store [{$name}] is not defined.");
        }

        $driverMethod = 'create' . ucfirst($config['driver']) . 'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        }

        throw new InvalidArgumentException("Cache driver [{$config['driver']}] is not supported.");
    }

    /**
     * Create an instance of the file cache driver.
     *
     * @param array $config
     * @return FileStore
     */
    protected function createFileDriver(array $config)
    {
        return new FileStore($config['path']);
    }

    /**
     * Create an instance of the array cache driver.
     *
     * @param array $config
     * @return ArrayStore
     */
    protected function createArrayDriver(array $config)
    {
        return new ArrayStore();
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->store()->$method(...$parameters);
    }
}

class FileStore
{
    protected string $path;

    public function __construct(string $path)
    {
        $this->path = rtrim($path, '/');
        if (! is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }

    public function get(string $key)
    {
        $file = $this->path . '/' . sha1($key);

        if (! file_exists($file)) {
            return null;
        }

        $content = file_get_contents($file);
        $data = @unserialize($content);

        if (!$data || time() >= $data['expiration']) {
            $this->forget($key);
            return null;
        }

        return $data['value'];
    }

    public function put(string $key, $value, int $seconds = 3600)
    {
        $file = $this->path . '/' . sha1($key);
        $data = [
            'value' => $value,
            'expiration' => time() + $seconds,
        ];

        return file_put_contents($file, serialize($data)) !== false;
    }

    public function forget(string $key)
    {
        $file = $this->path . '/' . sha1($key);
        if (file_exists($file)) {
            @unlink($file);
        }
        return true;
    }

    public function remember(string $key, int $seconds, Closure $callback)
    {
        if (! is_null($value = $this->get($key))) {
            return $value;
        }

        $this->put($key, $value = $callback(), $seconds);

        return $value;
    }
}

class ArrayStore
{
    protected array $storage = [];

    public function get(string $key)
    {
        if (! isset($this->storage[$key])) {
            return null;
        }

        if (time() >= $this->storage[$key]['expiration']) {
            unset($this->storage[$key]);
            return null;
        }

        return $this->storage[$key]['value'];
    }

    public function put(string $key, $value, int $seconds = 3600)
    {
        $this->storage[$key] = [
            'value' => $value,
            'expiration' => time() + $seconds,
        ];
        return true;
    }

    public function forget(string $key)
    {
        unset($this->storage[$key]);
        return true;
    }

    public function remember(string $key, int $seconds, Closure $callback)
    {
        if (! is_null($value = $this->get($key))) {
            return $value;
        }

        $this->put($key, $value = $callback(), $seconds);

        return $value;
    }
}
