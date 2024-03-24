<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Pages\InMemoryPage;
use Hyde\Support\Models\PageRoute;
use Hyde\Testing\TestsBladeViews;
use Hyde\Testing\Support\TestView;
use Illuminate\View\ComponentAttributeBag;
use Hyde\Framework\Features\Navigation\NavigationItem;
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
        return $this->view(view('hyde::components.navigation.navigation-link', [
            'item' => NavigationItem::create(new PageRoute(new InMemoryPage('foo')), 'Foo'),
            'attributes' => new ComponentAttributeBag(),
        ]));
    }

    public function testComponentRenders()
    {
        $this->testView()->assertHasElement('<a>');
    }

    public function testComponentLinksToRouteDestination()
    {
        $this->testView()->assertAttributeIs('href="foo.html"');
    }

    public function testComponentResolvesRelativeLinksForRoutes()
    {
        $this->mockCurrentPage('foo/bar');

        $this->testView()->assertAttributeIs('href="../foo.html"');
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
        $this->mockCurrentPage('foo');

        $this->testView()
            ->assertSee('current')
            ->assertHasAttribute('aria-current')
            ->assertAttributeIs('aria-current="page"');
    }

    public function testComponentDoesNotHaveActiveClassWhenNotActive()
    {
        $this->testView()
            ->assertHasClass('navigation-link')
            ->assertDoesNotHaveClass('navigation-link-active');
    }

    public function testComponentHasActiveClassWhenActive()
    {
        $this->mockCurrentPage('foo');

        $this->testView()
            ->assertHasClass('navigation-link')
            ->assertHasClass('navigation-link-active');
    }
}
