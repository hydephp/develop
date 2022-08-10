<?php

namespace Hyde\Framework\Views\Components;

use Hyde\Framework\Hyde;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LinkComponent extends Component
{
    public string $href;

    public function __construct(string $href)
    {
        $this->href = Hyde::relativeLink($href);
    }

    public function render(): View|Factory
    {
        return view('hyde::components.link');
    }
}
