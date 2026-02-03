<?php

namespace Nano\Framework\Middleware;

use Psr\Http\Message\ResponseInterface;
use Nano\Framework\Http\Request;
use Nano\Framework\Middleware\MiddlewareInterface;
use Nano\Framework\Auth;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;

class Authenticate implements MiddlewareInterface
{
    protected ?string $guard = null;

    public function __construct(?string $guard = null)
    {
        $this->guard = $guard;
    }

    public function process(Request $request, callable $next): ResponseInterface
    {
        $auth = new Auth();

        if ($this->guard) {
            $auth->shouldUse($this->guard);
        }

        if (! $auth->check()) {
            return $this->unauthenticated($request);
        }

        // Add user to request attribute for easy access in controllers
        // Note: Our fluent Request delegates __call to the PSR-7 request
        $psrRequest = $request->getPsrRequest()->withAttribute('user', $auth->user());

        // We need to re-wrap it because Diactoros methods return NEW instances
        $request = new Request($psrRequest);

        return $next($request);
    }

    protected function unauthenticated(Request $request): ResponseInterface
    {
        if ($request->header('Accept') === 'application/json' || $this->guard === 'api') {
            return new JsonResponse(['message' => 'Unauthenticated.'], 401);
        }

        return new RedirectResponse('/login');
    }
}
