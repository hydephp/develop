<?php

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Framework\Models\NavItem;
use Hyde\Testing\TestCase;

/**
 * @see resources/views/components/navigation/navigation-link.blade.php
 */
class NavigationLinkViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockRoute();
        $this->mockPage();
    }

    protected function render(?NavItem $item = null): string
    {
        return view('hyde::components.navigation.navigation-link', [
            'item' => $item ?? NavItem::toLink('foo.html', 'Foo'),
        ])->render();
    }

    public function test_component_links_to_route_destination()
    {
        $this->assertStringContainsString('href="foo.html"', $this->render());
    }

    public function test_component_uses_title()
    {
        $this->assertStringContainsString('Foo', $this->render());
    }
}
