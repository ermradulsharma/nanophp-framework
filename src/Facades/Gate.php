<?php

namespace Nano\Framework\Facades;

use Nano\Framework\Facade;

class Gate extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'gate';
    }
}
