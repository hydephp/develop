<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Testing\TestsBladeViews;
use Hyde\Testing\Support\TestView;
use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Features\Navigation\NavItem;
use Hyde\Testing\TestCase;

/**
 * @see resources/views/components/navigation/navigation-link.blade.php
 */
class NavigationLinkViewTest extends TestCase
{
    use TestsBladeViews;

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

    protected function testView(?NavItem $item = null): TestView
    {
        return $this->test(view('hyde::components.navigation.navigation-link', [
            'item' => $item ?? NavItem::forLink('foo.html', 'Foo'),
        ]));
    }

    public function testComponentLinksToRouteDestination()
    {
        $this->testView()->assertAttributeIs('href', 'foo.html');
    }

    public function testComponentUsesTitle()
    {
        $this->testView()->assertTextIs('Foo');
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
}
