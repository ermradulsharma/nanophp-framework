<?php

namespace Nano\Framework;

abstract class ViewComponent
{
    /**
     * Get the view / contents that represent the component.
     */
    abstract public function render();

    /**
     * Resolve the component to HTML.
     */
    public function __toString(): string
    {
        return (string) $this->render();
    }
}

