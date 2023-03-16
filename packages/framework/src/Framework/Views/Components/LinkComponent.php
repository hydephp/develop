<?php

declare(strict_types=1);

namespace Hyde\Framework\Views\Components;

use Hyde\Hyde;
use Illuminate\Support\Facades\View;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\View\Component;
use Illuminate\View\Factory;

class LinkComponent extends Component
{
    public readonly string $href;

    public function __construct(string $href)
    {
        $this->href = Hyde::relativeLink($href);
    }

    /** @interitDoc */
    public function render(): Factory|ViewContract
    {
        return View::make('hyde::components.link');
    }
}
