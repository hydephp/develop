<?php

namespace Hyde\Framework\Views\Components;

use Hyde\Framework\Hyde;
use Hyde\Framework\Contracts\RouteContract;
use Illuminate\Support\Facades\View;
use Illuminate\View\Component;

class LinkComponent extends Component
{
    public string $href;

    public function __construct(string|RouteContract $href)
    {
        $this->href = Hyde::relativeLink($href, View::shared('currentPage'));
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('hyde::components.link');
    }
}
