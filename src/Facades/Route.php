<?php

namespace Nano\Framework\Facades;

use Nano\Framework\Facade;

class Route extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Nano\Framework\Router::class;
    }
}

