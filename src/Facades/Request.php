<?php

namespace Nano\Framework\Facades;

use Nano\Framework\Facade;

class Request extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'request';
    }
}
