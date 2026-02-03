<?php

namespace Nano\Framework\Auth;

use Nano\Framework\Models\User;

class TokenGuard implements Guard
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

        $token = $this->getTokenFromRequest();

        if (! empty($token)) {
            $accessToken = PersonalAccessToken::findToken($token);

            if ($accessToken) {
                if ($accessToken->expired()) {
                    return $this->user = null;
                }

                $accessToken->last_used_at = new \DateTime();
                $accessToken->save();

                $this->user = $accessToken->tokenable();

                if ($this->user) {
                    $this->user->withAccessToken($accessToken);
                }
            }
        }

        return $this->user;
    }

    /**
     * Get the token from the request.
     *
     * @return string|null
     */
    protected function getTokenFromRequest()
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
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

        return null;
    }

    /**
     * Validate a user's credentials.
     *
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = []): bool
    {
        // Token guard doesn't validate credentials normally
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
        return false;
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
