<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestsBladeViews;
use Hyde\Pages\DocumentationPage;
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
        $this->mockRoute();

        Hyde::routes()->addRoute(new Route(new DocumentationPage('foo')));

        return $this->test(view('hyde::components.docs.sidebar-items', [
            'sidebar' => NavigationMenuGenerator::handle(DocumentationSidebar::class),
        ]));
    }

    public function testComponentRenders()
    {
        $this->testView()->assertHasElement('#sidebar-items');
    }

    public function testTypeAnnotationIsNotPresentInHtml()
    {
        $this->testView()->assertDontSee('@var')->assertDontSee('$group');
    }
}
