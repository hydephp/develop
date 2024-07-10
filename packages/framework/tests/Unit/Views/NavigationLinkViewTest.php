<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Pages\InMemoryPage;
use Hyde\Support\Models\Route;
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
            'item' => NavigationItem::create(new Route(new InMemoryPage('foo')), 'Foo'),
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

    public function testComponentState()
    {
        $this->mockCurrentPage('foo');

        $view = $this->testView();

        $expected = <<<'HTML'
        <a href="foo.html" aria-current="page" class="navigation-link block my-2 md:my-0 md:inline-block py-1 text-gray-700 hover:text-gray-900 dark:text-gray-100 navigation-link-active border-l-4 border-indigo-500 md:border-none font-medium -ml-6 pl-5 md:ml-0 md:pl-0 bg-gray-100 dark:bg-gray-800 md:bg-transparent dark:md:bg-transparent">Foo</a>
        HTML;

        $this->assertSame($expected, $view->getRendered());
    }
}
