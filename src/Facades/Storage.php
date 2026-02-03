<?php

namespace Nano\Framework\Facades;

use Nano\Framework\Facade;

/**
 * @method static \Illuminate\Filesystem\FilesystemAdapter disk(string|null $name = null)
 * @method static bool exists(string $path)
 * @method static string get(string $path)
 * @method static bool put(string $path, string $contents, mixed $options = [])
 * @method static bool delete(string $path)
 * 
 * @see \Nano\Framework\Filesystem\StorageManager
 */
class Storage extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'storage';
    }
}
