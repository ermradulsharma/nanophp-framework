<?php

namespace Nano\Framework\Auth\Traits;

use Nano\Framework\Facades\Gate;

trait Authorizable
{
    /**
     * Determine if the user has a given ability.
     *
     * @param string $ability
     * @param array|mixed $arguments
     * @return bool
     */
    public function can(string $ability, mixed $arguments = []): bool
    {
        return Gate::allows($ability, $arguments);
    }

    /**
     * Determine if the user does not have a given ability.
     *
     * @param string $ability
     * @param array|mixed $arguments
     * @return bool
     */
    public function cannot(string $ability, mixed $arguments = []): bool
    {
        return Gate::denies($ability, $arguments);
    }
}
