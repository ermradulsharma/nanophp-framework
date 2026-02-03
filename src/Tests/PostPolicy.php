<?php

namespace Nano\Framework\Tests;

use Nano\Framework\Models\User;

class PostPolicy
{
    public function update(User $user, $post)
    {
        return $user->id === $post['user_id'];
    }

    public function delete(User $user, $post)
    {
        return $user->isAdmin === true;
    }
}
