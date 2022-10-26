<?php

declare(strict_types=1);

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

    /** @interitDoc */
    public function render(): Factory|View
    {
        return view('hyde::components.link');
    }
}
