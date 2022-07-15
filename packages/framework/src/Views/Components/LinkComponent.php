<?php

namespace Hyde\Framework\Views\Components;

use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Route;
use Illuminate\Support\Facades\View;
use Illuminate\View\Component;

class LinkComponent extends Component
{
    public string $href;

    public function __construct(string|Route $href)
    {
        $this->href = Hyde::relativeLink($href, View::shared('currentPage'));
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('hyde::components.link');
    }
}
