<?php

namespace Nano\Framework\Middleware;

use Psr\Http\Message\ResponseInterface;
use Nano\Framework\Http\Request;

class SecurityHeadersMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $next): ResponseInterface
    {
        $response = $next($request);

        return $response
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('X-Frame-Options', 'SAMEORIGIN')
            ->withHeader('X-XSS-Protection', '1; mode=block')
            ->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->withHeader('Content-Security-Policy', "default-src 'self' 'unsafe-inline' 'unsafe-eval' https: data: http://localhost:5173 ws://localhost:5173;");
    }
}
