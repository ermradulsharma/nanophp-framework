<?php

namespace Nano\Framework\Middleware;

use Psr\Http\Message\ResponseInterface;
use Nano\Framework\Http\Request;

class TestMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $next): ResponseInterface
    {
        $response = $next($request);
        return $response->withHeader('X-Nano-Middleware', 'Worked');
    }
}
