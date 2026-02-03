<?php

namespace Nano\Framework;

use RuntimeException;

abstract class Facade
{
    /**
     * The service container instance.
     *
     * @var \DI\Container
     */
    protected static $container;

    /**
     * Set the container instance.
     *
     * @param  mixed  $container
     * @return void
     */
    public static function setContainer($container)
    {
        static::$container = $container;
        $GLOBALS['__nano_facade_container'] = $container;
    }

    /**
     * Resolve the facade root instance from the container.
     *
     * @return mixed
     */
    public static function getFacadeRoot()
    {
        $container = static::$container ?? ($GLOBALS['__nano_facade_container'] ?? null);

        if (!$container) {
            $app = Application::getInstance();
            if ($app) {
                $container = $app->getContainer();
            }
        }

        if (!$container) {
            throw new RuntimeException('Facade container not set.');
        }

        return $container->get(static::getFacadeAccessor());
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        throw new RuntimeException('Facade does not implement getFacadeAccessor method.');
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::getFacadeRoot();

        if (! $instance) {
            throw new RuntimeException('A facade root has not been set.');
        }

        return $instance->$method(...$args);
    }
}
