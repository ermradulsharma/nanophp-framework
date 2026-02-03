<?php

namespace Nano\Framework\Facades;

use Nano\Framework\Facade;

/**
 * @method static mixed get(string $key)
 * @method static bool put(string $key, mixed $value, int $seconds = 3600)
 * @method static bool forget(string $key)
 * @method static mixed remember(string $key, int $seconds, \Closure $callback)
 * @method static \Nano\Framework\Cache\CacheManager store(string|null $name = null)
 * 
 * @see \Nano\Framework\Cache\CacheManager
 */
class Cache extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'cache';
    }
}
