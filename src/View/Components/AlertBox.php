<?php

namespace Nano\Framework\View\Components;

use Nano\Framework\ViewComponent;

class AlertBox extends ViewComponent
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.alert-box');
    }
}
