<?php

namespace Hyde\Framework\Views\Components;

use Hyde\Framework\Hyde;
use Illuminate\View\Component;

class LinkComponent extends Component
{
    public string $href;

    public function __construct(string $href)
    {
        $this->href = Hyde::relativeLink($href);
    }

    public function render(): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
    {
        return view('hyde::components.link');
    }
}
