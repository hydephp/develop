<?php

namespace Hyde\Framework\Views\Components;

use Hyde\Framework\Models\Route;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LinkComponent extends Component
{
    public string $href;

    public function __construct(string|Route $href)
    {
        $this->href = $href;
    }

    public function render(): View
    {
        return view('hyde::components.link');
    }
}
