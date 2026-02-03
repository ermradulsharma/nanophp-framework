<?php

namespace Nano\Framework\Auth\Traits;

use Nano\Framework\Facades\Gate;
use Exception;

trait AuthorizesRequests
{
    /**
     * Authorize a given action for the current user.
     *
     * @param string $ability
     * @param array|mixed $arguments
     * @return bool
     *
     * @throws Exception
     */
    public function authorize(string $ability, mixed $arguments = []): bool
    {
        if (Gate::denies($ability, $arguments)) {
            throw new Exception("This action is unauthorized.", 403);
        }

        return true;
    }
}
