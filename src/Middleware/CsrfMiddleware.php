<?php

namespace Nano\Framework\Middleware;

use Psr\Http\Message\ResponseInterface;
use Nano\Framework\Http\Request;
use Laminas\Diactoros\Response\TextResponse;

class CsrfMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $next): ResponseInterface
    {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Generate token if not exists
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $method = $request->method();
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $token = $request->input('_token') ?? $request->header('X-CSRF-TOKEN');

            if (!$token || !hash_equals($_SESSION['csrf_token'], $token)) {
                return new TextResponse('419 Page Expired (CSRF Token Mismatch)', 419);
            }
        }

        return $next($request);
    }
}
