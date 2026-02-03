<?php

namespace Nano\Framework\Auth;

use Nano\Framework\Model;
use Nano\Framework\Models\User;

class PersonalAccessToken extends Model
{
    protected $table = 'personal_access_tokens';

    protected $fillable = [
        'name',
        'token',
        'abilities',
        'last_used_at',
        'tokenable_id',
        'tokenable_type',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'abilities' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the tokenable model that the access token belongs to.
     *
     * @return mixed
     */
    public function tokenable()
    {
        return User::find($this->tokenable_id);
    }

    /**
     * Find the token instance matching the given token.
     *
     * @param string $token
     * @return static|null
     */
    public static function findToken($token)
    {
        if (str_contains($token, '|')) {
            $token = explode('|', $token, 2)[1];
        }

        return static::where('token', hash('sha256', $token))->first();
    }

    /**
     * Determine if the token has the given ability.
     *
     * @param string $ability
     * @return bool
     */
    public function can($ability)
    {
        $abilities = $this->abilities;

        if (is_string($abilities)) {
            $abilities = json_decode($abilities, true) ?: [];
        }

        if (is_array($abilities) && in_array('*', $abilities)) {
            return true;
        }

        return is_array($abilities) && in_array($ability, $abilities);
    }

    /**
     * Determine if the token has expired.
     *
     * @return bool
     */
    public function expired()
    {
        if (! $this->expires_at) {
            return false;
        }

        $expiresAt = $this->expires_at;

        if (is_string($expiresAt)) {
            try {
                $expiresAt = new \DateTime($expiresAt);
            } catch (\Exception $e) {
                return false;
            }
        }

        if ($expiresAt instanceof \DateTimeInterface) {
            return $expiresAt < new \DateTime();
        }

        return false;
    }
}
