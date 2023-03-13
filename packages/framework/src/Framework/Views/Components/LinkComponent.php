<?php

declare(strict_types=1);

namespace Hyde\Framework\Views\Components;

use Hyde\Hyde;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

use function view;

class LinkComponent extends Component
{
    public readonly string $href;

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
