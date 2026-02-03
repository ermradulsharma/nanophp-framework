<?php

namespace Nano\Framework\Auth;

use Nano\Framework\Auth\PersonalAccessToken;

trait HasApiTokens
{
    /**
     * The access token currently associated with the user.
     *
     * @var \Nano\Framework\Auth\PersonalAccessToken|null
     */
    protected $accessToken;

    /**
     * Create a new personal access token for the user.
     *
     * @param string $name
     * @param array $abilities
     * @param \DateTimeInterface|null $expiresAt
     * @return \Nano\Framework\Auth\NewAccessToken
     */
    public function createToken(string $name, array $abilities = ['*'], $expiresAt = null)
    {
        $token = bin2hex(random_bytes(40));

        $accessToken = new PersonalAccessToken([
            'name' => $name,
            'token' => hash('sha256', $token),
            'abilities' => $abilities,
            'tokenable_id' => $this->id,
            'tokenable_type' => get_class($this),
            'expires_at' => $expiresAt,
        ]);

        $accessToken->save();

        return new NewAccessToken($accessToken, $accessToken->id . '|' . $token);
    }

    /**
     * Get the access tokens for the user.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function tokens()
    {
        return PersonalAccessToken::where('tokenable_id', $this->id)
            ->where('tokenable_type', get_class($this))
            ->get();
    }

    /**
     * Get the access token currently associated with the user.
     *
     * @return \Nano\Framework\Auth\PersonalAccessToken|null
     */
    public function currentAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set the current access token for the user.
     *
     * @param \Nano\Framework\Auth\PersonalAccessToken $accessToken
     * @return $this
     */
    public function withAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Determine if the current API token has a given ability.
     *
     * @param string $ability
     * @return bool
     */
    public function tokenCan(string $ability)
    {
        return $this->accessToken ? $this->accessToken->can($ability) : false;
    }

    /**
     * Revoke all of the user's tokens.
     *
     * @return bool
     */
    public function revokeTokens()
    {
        return PersonalAccessToken::where('tokenable_id', $this->id)
            ->where('tokenable_type', get_class($this))
            ->delete();
    }

    /**
     * Revoke the current access token.
     *
     * @return bool
     */
    public function revokeCurrentToken()
    {
        return $this->accessToken ? $this->accessToken->delete() : false;
    }
}

class NewAccessToken
{
    /**
     * The access token instance.
     *
     * @var \Nano\Framework\Auth\PersonalAccessToken
     */
    public $accessToken;

    /**
     * The plain text version of the token.
     *
     * @var string
     */
    public $plainTextToken;

    /**
     * Create a new access token result.
     *
     * @param \Nano\Framework\Auth\PersonalAccessToken $accessToken
     * @param string $plainTextToken
     * @return void
     */
    public function __construct(PersonalAccessToken $accessToken, string $plainTextToken)
    {
        $this->accessToken = $accessToken;
        $this->plainTextToken = $plainTextToken;
    }
}
