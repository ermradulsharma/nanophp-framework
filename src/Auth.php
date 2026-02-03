<?php

namespace Nano\Framework;

use Nano\Framework\Models\User;
use Nano\Framework\Auth\Guard;
use Nano\Framework\Auth\SessionGuard;
use Nano\Framework\Auth\TokenGuard;

class Auth
{
    /**
     * The array of created "drivers".
     *
     * @var array
     */
    protected array $guards = [];

    /**
     * The default guard name.
     *
     * @var string
     */
    protected string $default = 'web';

    /**
     * Attempt to authenticate a user using the default guard.
     */
    public function attempt(array $credentials): bool
    {
        return $this->guard()->attempt($credentials);
    }

    /**
     * Log the given user into the application.
     */
    public function login(User $user): void
    {
        $guard = $this->guard();

        if ($guard instanceof SessionGuard) {
            $guard->login($user);
        }
    }

    /**
     * Log the user out of the application.
     */
    public function logout(): void
    {
        $guard = $this->guard();

        if ($guard instanceof SessionGuard) {
            $guard->logout();
        }
    }

    /**
     * Check if the current user is authenticated.
     */
    public function check(): bool
    {
        return $this->guard()->check();
    }

    /**
     * Get the currently authenticated user.
     */
    public function user(): ?User
    {
        return $this->guard()->user();
    }

    /**
     * Get the ID for the currently authenticated user.
     */
    public function id(): ?int
    {
        return $this->guard()->id();
    }

    /**
     * Get a guard instance by name.
     *
     * @param string|null $name
     * @return \Nano\Framework\Auth\Guard
     */
    public function guard(?string $name = null): Guard
    {
        $name = $name ?: $this->default;

        if (! isset($this->guards[$name])) {
            $this->guards[$name] = $this->resolve($name);
        }

        return $this->guards[$name];
    }

    /**
     * Resolve the given guard.
     *
     * @param string $name
     * @return \Nano\Framework\Auth\Guard
     */
    protected function resolve(string $name): Guard
    {
        if ($name === 'api') {
            return new TokenGuard();
        }

        return new SessionGuard();
    }

    /**
     * Set the default guard.
     *
     * @param string $name
     * @return void
     */
    public function shouldUse(string $name): void
    {
        $this->default = $name;
    }
}
