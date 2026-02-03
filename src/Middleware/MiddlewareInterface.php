<?php

namespace Nano\Framework\Middleware;

use Psr\Http\Message\ResponseInterface;
use Nano\Framework\Http\Request;

interface MiddlewareInterface
{
    public function process(Request $request, callable $next): ResponseInterface;
}
