<?php

namespace Nano\Framework\Listeners;

use Nano\Framework\Events\UserRegistered;

class SendWelcomeEmail
{
    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        //
    }
}
