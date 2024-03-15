<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Pages\InMemoryPage;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestsBladeViews;
use Hyde\Testing\Support\TestView;
use Illuminate\View\ComponentAttributeBag;
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

    protected function testView(): TestView
    {
        return $this->test(view('hyde::components.navigation.navigation-link', [
            'item' => NavItem::forRoute(new Route(new InMemoryPage('foo')), 'Foo'),
            'attributes' => new ComponentAttributeBag(),
        ]));
    }

    public function testComponentLinksToRouteDestination()
    {
        $this->testView()->assertAttributeIs('href', 'foo.html');
    }

    public function testComponentResolvesRelativeLinksForRoutes()
    {
        $this->mockCurrentPage('foo/bar');
        $this->testView()->assertAttributeIs('href', '../foo.html');
    }

    public function testComponentUsesTitle()
    {
        $this->testView()->assertTextIs('Foo');
    }

    public function testComponentDoesNotHaveCurrentAttributesWhenCurrentRouteDoesNotMatch()
    {
        $this->testView()
            ->assertDontSee('current')
            ->assertDoesNotHaveAttribute('aria-current');
    }

    public function testComponentIsCurrentWhenCurrentRouteMatches()
    {
        $this->mockCurrentPage('foo')
            ->testView()
            ->assertSee('current')
            ->assertHasAttribute('aria-current')
            ->assertAttributeIs('aria-current="page"');
    }

    public function testComponentDoesNotHaveActiveClassWhenNotActive()
    {
        $this->testView()
            ->assertSee('navigation-link ')
            ->assertDontSee('navigation-link-active');
    }

    public function testComponentHasActiveClassWhenActive()
    {
        $this->mockCurrentPage('foo')
            ->testView()
            ->assertSee('navigation-link ')
            ->assertSee('navigation-link-active');
    }
}
