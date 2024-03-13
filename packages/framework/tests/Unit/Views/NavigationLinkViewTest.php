<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Features\Navigation\NavItem;
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
            'item' => $item ?? NavItem::forLink('foo.html', 'Foo'),
        ])->render();
    }

    public function testComponentLinksToRouteDestination()
    {
        $this->assertStringContainsString('href="foo.html"', $this->render());
    }

    public function testComponentUsesTitle()
    {
        $this->assertStringContainsString('Foo', $this->render());
    }

    public function testComponentIsCurrentWhenCurrentRouteMatches()
    {
        $this->mockRoute(Routes::get('index'));
        $this->assertStringContainsString('current', $this->render(NavItem::forRoute(Routes::get('index'), 'Home')));
    }

    public function testComponentHasAriaCurrentWhenCurrentRouteMatches()
    {
        $this->mockRoute(Routes::get('index'));
        $this->assertStringContainsString('aria-current="page"', $this->render(NavItem::forRoute(Routes::get('index'), 'Home')));
    }

    public function testComponentDoesNotHaveActiveClassWhenNotActive()
    {
        $render = $this->render(NavItem::forRoute(Routes::get('index'), 'Home'));
        $this->assertStringContainsString('navigation-link ', $render);
        $this->assertStringNotContainsString('navigation-link-active', $render);
    }

    public function testComponentHasActiveClassWhenActive()
    {
        $this->mockRoute(Routes::get('index'));
        $render = $this->render(NavItem::forRoute(Routes::get('index'), 'Home'));
        $this->assertStringContainsString('navigation-link ', $render);
        $this->assertStringContainsString('navigation-link-active', $render);
    }
}
