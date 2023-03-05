<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Foundation\HydeKernel;
use Hyde\Testing\TestCase;

/**
 * Very high level test of the sidebar views and their combinations of layouts.
 */
class SidebarViewTest extends TestCase
{
    public function testBaseSidebar()
    {
        $html = $this->renderComponent(view('hyde::components.docs.sidebar'));
    }

    protected function renderComponent(\Illuminate\Contracts\View\View $view): string
    {
        return $view->render();
    }
}
