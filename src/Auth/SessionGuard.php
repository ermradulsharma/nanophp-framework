<?php

namespace Nano\Framework\Auth;

use Nano\Framework\Models\User;

class SessionGuard implements Guard
{
    /**
     * The currently authenticated user.
     *
     * @var \Nano\Framework\Models\User|null
     */
    protected ?User $user = null;

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check(): bool
    {
        return ! is_null($this->user());
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest(): bool
    {
        return ! $this->check();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Nano\Framework\Models\User|null
     */
    public function user(): ?User
    {
        if (! is_null($this->user)) {
            return $this->user;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id = $_SESSION['user_id'] ?? null;

        if (! is_null($id)) {
            $this->user = User::query()->find($id);
        }

        return $this->user;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     */
    public function id(): int|string|null
    {
        if ($this->user()) {
            return $this->user()->getAuthIdentifier();
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Validate a user's credentials.
     *
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = []): bool
    {
        $user = User::where('email', $credentials['email'])->first();

        if ($user && password_verify($credentials['password'], $user->password)) {
            return true;
        }

        return false;
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param array $credentials
     * @return bool
     */
    public function attempt(array $credentials = []): bool
    {
        $user = User::where('email', $credentials['email'])->first();

        if ($user && password_verify($credentials['password'], $user->password)) {
            $this->login($user);
            return true;
        }

        return false;
    }

    /**
     * Log a user into the application.
     *
     * @param \Nano\Framework\Models\User $user
     * @return void
     */
    public function login(User $user): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user_id'] = $user->id;
        $this->setUser($user);
    }

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION['user_id']);
        session_destroy();

        $this->user = null;
    }

    /**
     * Set the current user.
     *
     * @param \Nano\Framework\Models\User $user
     * @return $this
     */
    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }
}
