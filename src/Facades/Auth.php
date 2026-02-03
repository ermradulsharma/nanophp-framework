<?php

namespace Nano\Framework\Facades;

use Nano\Framework\Facade;

/**
 * @method static bool attempt(array $credentials)
 * @method static void login(\Nano\Framework\Models\User $user)
 * @method static void logout()
 * @method static bool check()
 * @method static \Nano\Framework\Models\User|null user()
 * @method static int|null id()
 */
class Auth extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'auth';
    }
}

