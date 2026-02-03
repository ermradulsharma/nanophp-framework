<?php

namespace Nano\Framework\Filesystem;

use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Container\Container;

class StorageManager extends FilesystemManager
{
    /**
     * Create a new filesystem manager instance.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        // Use the global container instance
        $container = Container::getInstance();

        // Ensure 'config' is available in the container as expected by FilesystemManager
        // It expects $app['config']['filesystems.disks.X'] or similar
        $container->instance('config', new class($config) implements \ArrayAccess {
            protected $config;
            public function __construct($config)
            {
                $this->config = $config;
            }
            public function offsetExists($offset): bool
            {
                return $offset === 'filesystems' || str_starts_with($offset, 'filesystems.');
            }
            public function offsetGet($offset): mixed
            {
                if ($offset === 'filesystems') return $this->config;
                if ($offset === 'filesystems.default') return $this->config['default'] ?? 'local';
                if ($offset === 'filesystems.disks') return $this->config['disks'] ?? [];
                if (str_starts_with($offset, 'filesystems.disks.')) {
                    $disk = substr($offset, 18);
                    return $this->config['disks'][$disk] ?? null;
                }
                return null;
            }
            public function offsetSet($offset, $value): void {}
            public function offsetUnset($offset): void {}
        });

        parent::__construct($container);
    }



    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->container['config']['filesystems.default'] ?? 'local';
    }

    /**
     * Handle dynamic methods calls into the default disk.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->disk()->$method(...$parameters);
    }
}
