<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Pages\InMemoryPage;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestsBladeViews;
use Hyde\Testing\Support\TestView;
use Hyde\Foundation\Facades\Routes;
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

    protected function render(?NavItem $item = null): string
    {
        return view('hyde::components.navigation.navigation-link', [
            'item' => $item ?? NavItem::forLink('foo.html', 'Foo'),
            'attributes' => new ComponentAttributeBag(),
        ])->render();
    }

    protected function testView(?NavItem $item = null): TestView
    {
        return $this->test(view('hyde::components.navigation.navigation-link', [
            'item' => $item ?? NavItem::forRoute(new Route(new InMemoryPage('foo')), 'Foo'),
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
            ->assertAttributeIs('aria-current', 'page');
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
