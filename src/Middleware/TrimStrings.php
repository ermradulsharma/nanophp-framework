<?php

namespace Nano\Framework\Middleware;

use Psr\Http\Message\ResponseInterface;
use Nano\Framework\Http\Request;

class TrimStrings implements MiddlewareInterface
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array
     */
    protected $except = [
        'password',
        'password_confirmation',
    ];

    /**
     * Process the incoming request.
     *
     * @param Request $request
     * @param callable $next
     * @return ResponseInterface
     */
    public function process(Request $request, callable $next): ResponseInterface
    {
        $all = $request->all();

        foreach ($all as $key => $value) {
            if (in_array($key, $this->except, true)) {
                continue;
            }

            if (is_string($value)) {
                $all[$key] = trim($value);
            }
        }

        // We can't easily modify the underlying PSR-7 request object's query/body
        // if they are immutable, but we can update our fluent Request wrapper's 
        // internal all() state if we allow it, or just re-wrap.

        // Actually, let's update Nano\Framework\Http\Request to support modifying data
        // For simplicity in this demo, we'll just re-wrap with a modified attribute 
        // OR better, we update Request.php to handle this.

        $request->merge($all);

        return $next($request);
    }
}
