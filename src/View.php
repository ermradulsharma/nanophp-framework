<?php

namespace Nano\Framework;

use Illuminate\Contracts\View\Factory as ViewFactory;

class View
{
    protected $factory;

    public function __construct(ViewFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Render a view template.
     */
    public function render(string $view, array $data = []): string
    {
        return $this->factory->make($view, $data)->render();
    }

    /**
     * Get the underlying factory.
     */
    public function getFactory(): ViewFactory
    {
        return $this->factory;
    }
}

