<?php

namespace Nano\Framework;

use Illuminate\Contracts\View\Factory as ViewFactory;
use App;

class Mailable
{
    protected string $view;
    protected string $subject;
    protected array $data = [];

    /**
     * Set the view for the email.
     */
    public function view(string $view, array $data = []): self
    {
        $this->view = $view;
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Set the subject for the email.
     */
    public function subject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Build the mailable. (To be overridden by child classes)
     */
    public function build()
    {
        return $this;
    }

    /**
     * Render the mailable to HTML.
     */
    public function render(): string
    {
        $this->build();
        return App::getContainer()->get(ViewFactory::class)->make($this->view, $this->data)->render();
    }
}

