<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Testing\TestCase;
use Hyde\Testing\TestsBladeViews;
use Hyde\Testing\Support\TestView;
use Hyde\Framework\Features\Navigation\DocumentationSidebar;
use Hyde\Framework\Features\Navigation\NavigationMenuGenerator;

/**
 * @see resources/views/components/docs/sidebar-items.blade.php
 */
class SidebarItemsViewTest extends TestCase
{
    use TestsBladeViews;

    protected function testView(): TestView
    {
        return $this->test(view('hyde::components.docs.sidebar-items', [
            'sidebar' => NavigationMenuGenerator::handle(DocumentationSidebar::class),
        ]));
    }

    public function testComponentRenders()
    {
        $this->testView()->assertHasElement('#sidebar-items');
    }
}
