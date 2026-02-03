<?php

namespace Nano\Framework\Communications\Sms;

class WelcomeSecuritySms
{
    protected string $to;
    protected string $message;

    public function __construct(string $to = '')
    {
        $this->to = $to;
    }

    /**
     * Set the recipient.
     */
    public function to(string $number): self
    {
        $this->to = $number;
        return $this;
    }

    /**
     * Set the message content.
     */
    public function content(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Send the SMS via your preferred gateway (Twilio/Vonage/etc).
     */
    public function send(): bool
    {
        if (empty($this->to) || empty($this->message)) {
            return false;
        }

        // Logic to hit SMS Gateway API
        // error_log("Sending SMS to {$this->to}: {$this->message}");
        
        return true;
    }
}
