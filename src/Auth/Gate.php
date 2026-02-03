<?php

namespace Nano\Framework\Auth;

use Nano\Framework\Models\User;
use Nano\Framework\Auth;
use Closure;
use Exception;
use RuntimeException;

class Gate
{
    /**
     * The auth instance.
     *
     * @var Auth
     */
    protected Auth $auth;

    /**
     * All of the defined abilities.
     *
     * @var array
     */
    protected array $abilities = [];

    /**
     * All of the defined policies.
     *
     * @var array
     */
    protected array $policies = [];

    /**
     * All of the before callbacks.
     *
     * @var array
     */
    protected array $beforeCallbacks = [];

    /**
     * All of the after callbacks.
     *
     * @var array
     */
    protected array $afterCallbacks = [];

    /**
     * Create a new gate instance.
     *
     * @param Auth $auth
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Define a new ability.
     *
     * @param string $ability
     * @param callable|string $callback
     * @return $this
     */
    public function define(string $ability, callable|string $callback): self
    {
        $this->abilities[$ability] = $callback;

        return $this;
    }

    /**
     * Register a policy for a class.
     *
     * @param string $class
     * @param string $policy
     * @return $this
     */
    public function policy(string $class, string $policy): self
    {
        $this->policies[$class] = $policy;

        return $this;
    }

    /**
     * Register a callback to be run before all gate checks.
     *
     * @param callable $callback
     * @return $this
     */
    public function before(callable $callback): self
    {
        $this->beforeCallbacks[] = $callback;

        return $this;
    }

    /**
     * Register a callback to be run after all gate checks.
     *
     * @param callable $callback
     * @return $this
     */
    public function after(callable $callback): self
    {
        $this->afterCallbacks[] = $callback;

        return $this;
    }

    /**
     * Determine if the given ability should be granted for the current user.
     *
     * @param string $ability
     * @param array|mixed $arguments
     * @return bool
     */
    public function allows(string $ability, mixed $arguments = []): bool
    {
        return $this->check($ability, $arguments);
    }

    /**
     * Determine if the given ability should be denied for the current user.
     *
     * @param string $ability
     * @param array|mixed $arguments
     * @return bool
     */
    public function denies(string $ability, mixed $arguments = []): bool
    {
        return ! $this->allows($ability, $arguments);
    }

    /**
     * Determine if the current user has a given ability.
     *
     * @param string $ability
     * @param array|mixed $arguments
     * @return bool
     */
    public function check(string $ability, mixed $arguments = []): bool
    {
        $user = $this->auth->user();

        if (! $user) {
            return false;
        }

        $arguments = is_array($arguments) ? $arguments : [$arguments];

        // 1. Run before callbacks
        foreach ($this->beforeCallbacks as $callback) {
            $result = $callback($user, $ability, $arguments);

            if ($result !== null) {
                return (bool) $result;
            }
        }

        // 2. Resolve the check (Ability or Policy)
        $result = $this->raw($user, $ability, $arguments);

        // 3. Run after callbacks
        foreach ($this->afterCallbacks as $callback) {
            $afterResult = $callback($user, $ability, $result, $arguments);

            if ($afterResult !== null) {
                return (bool) $afterResult;
            }
        }

        return (bool) $result;
    }

    /**
     * Get the raw result for the given ability.
     *
     * @param User $user
     * @param string $ability
     * @param array $arguments
     * @return bool
     */
    protected function raw(User $user, string $ability, array $arguments): bool
    {
        // Try to resolve as a direct ability
        if (isset($this->abilities[$ability])) {
            $callback = $this->abilities[$ability];
            return $this->callAbilityCallback($user, $callback, $arguments);
        }

        // Try to resolve as a policy
        if (str_contains($ability, '@')) {
            [$class, $method] = explode('@', $ability);
            return $this->callPolicyMethod($user, $class, $method, $arguments);
        }

        // Auto-check if the first argument has a registered policy
        if (! empty($arguments) && is_object($arguments[0])) {
            $class = get_class($arguments[0]);
            if (isset($this->policies[$class])) {
                return $this->callPolicyMethod($user, $this->policies[$class], $ability, $arguments);
            }
        }

        return false;
    }

    /**
     * Call the callback for the ability.
     *
     * @param User $user
     * @param callable|string $callback
     * @param array $arguments
     * @return bool
     */
    protected function callAbilityCallback(User $user, callable|string $callback, array $arguments): bool
    {
        if ($callback instanceof Closure) {
            return (bool) $callback($user, ...$arguments);
        }

        if (is_string($callback) && str_contains($callback, '@')) {
            [$class, $method] = explode('@', $callback);
            $instance = new $class();
            return (bool) $instance->$method($user, ...$arguments);
        }

        return false;
    }

    /**
     * Call the policy method.
     *
     * @param User $user
     * @param string $policy
     * @param string $method
     * @param array $arguments
     * @return bool
     */
    protected function callPolicyMethod(User $user, string $policy, string $method, array $arguments): bool
    {
        if (! class_exists($policy)) {
            throw new RuntimeException("Policy class not found: {$policy}");
        }

        $instance = new $policy();

        if (! method_exists($instance, $method)) {
            return false;
        }

        return (bool) $instance->$method($user, ...$arguments);
    }
}
