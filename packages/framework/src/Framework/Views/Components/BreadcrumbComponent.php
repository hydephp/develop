<?php

declare(strict_types=1);

namespace Hyde\Framework\Views\Components;

use Hyde\Hyde;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BreadcrumbComponent extends Component
{
    public array $breadcrumbs;

    public function __construct()
    {
        $this->breadcrumbs = Hyde::currentRoute()->getBreadcrumbs();
    }

    /** @interitDoc */
    public function render(): Factory|View
    {
        return view('hyde::components.breadcrumb');
    }
}
