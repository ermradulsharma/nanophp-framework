<?php

namespace Nano\Framework\Auth;

use Nano\Framework\Models\User;

interface Guard
{
    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check(): bool;

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest(): bool;

    /**
     * Get the currently authenticated user.
     *
     * @return \Nano\Framework\Models\User|null
     */
    public function user(): ?User;

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     */
    public function id(): int|string|null;

    /**
     * Validate a user's credentials.
     *
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = []): bool;

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param array $credentials
     * @return bool
     */
    public function attempt(array $credentials = []): bool;

    /**
     * Set the current user.
     *
     * @param \Nano\Framework\Models\User $user
     * @return $this
     */
    public function setUser(User $user): static;
}
