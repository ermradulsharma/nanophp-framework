<?php

namespace Nano\Framework\Notifications;

class OrderProcessed
{
    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(mixed $notifiable)
    {
        // return (new Mailable)->to($notifiable->email);
    }

    /**
     * Get the array representation of the notification (for database/api).
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            //
        ];
    }
}
