<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Support\Models\Route;
use Hyde\Foundation\Facades\Routes;
use Hyde\Support\Models\ExternalRoute;
use Hyde\Framework\Features\Navigation\NavigationMenu;
use Hyde\Framework\Features\Navigation\NavItem;
use Hyde\Pages\MarkdownPage;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;
use Hyde\Framework\Features\Navigation\GeneratesMainNavigationMenu;

/**
 * @covers \Hyde\Framework\Features\Navigation\GeneratesMainNavigationMenu
 * @covers \Hyde\Framework\Features\Navigation\NavigationMenu
 *
 * @see \Hyde\Framework\Testing\Unit\NavigationMenuUnitTest
 */
class NavigationMenuTest extends TestCase
{
    public function testConstructor()
    {
        $this->assertInstanceOf(NavigationMenu::class, $this->createNavigationMenu());
    }

    public function testGenerateMethodCreatesCollectionOfNavItems()
    {
        $this->assertInstanceOf(Collection::class, $this->createNavigationMenu()->getItems());
        $this->assertContainsOnlyInstancesOf(NavItem::class, $this->createNavigationMenu()->getItems());
    }

    public function testGetItemsReturnsItems()
    {
        $this->assertEquals(collect([
            NavItem::fromRoute(Routes::get('index')),
        ]), $this->createNavigationMenu()->getItems());
    }

    public function testItemsAreSortedByPriority()
    {
        Routes::addRoute(new Route(new MarkdownPage('foo', ['navigation.priority' => 1])));
        Routes::addRoute(new Route(new MarkdownPage('bar', ['navigation.priority' => 2])));
        Routes::addRoute(new Route(new MarkdownPage('baz', ['navigation.priority' => 3])));

        $this->assertSame(['Home', 'Foo', 'Bar', 'Baz'], $this->createNavigationMenu()->getItems()->pluck('label')->toArray());
    }

    public function testItemsWithHiddenPropertySetToTrueAreNotAdded()
    {
        Routes::addRoute(new Route(new MarkdownPage('foo', ['navigation.hidden' => true])));
        Routes::addRoute(new Route(new MarkdownPage('bar', ['navigation.hidden' => false])));

        $this->assertSame(['Home', 'Bar'], $this->createNavigationMenu()->getItems()->pluck('label')->toArray());
    }

    public function testCreatedCollectionIsSortedByNavigationMenuPriority()
    {
        $this->file('_pages/foo.md');
        $this->file('_docs/index.md');

        $menu = $this->createNavigationMenu();

        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::fromRoute(Routes::get('foo')),
            NavItem::fromRoute(Routes::get('docs/index')),
        ]);

        $this->assertCount(count($expected), $menu->getItems());
        $this->assertEquals($expected, $menu->getItems());
    }

    public function testIsSortedAutomaticallyWhenUsingNavigationMenuCreate()
    {
        $this->file('_pages/foo.md');

        $menu = $this->createNavigationMenu();

        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::fromRoute(Routes::get('foo')),
        ]);

        $this->assertCount(count($expected), $menu->getItems());
        $this->assertEquals($expected, $menu->getItems());
    }

    public function testExternalLinkCanBeAddedInConfig()
    {
        config(['hyde.navigation.custom' => [NavItem::forLink('https://example.com', 'foo')]]);

        $menu = $this->createNavigationMenu();

        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::forLink('https://example.com', 'foo'),
        ]);

        $this->assertCount(count($expected), $menu->getItems());
        $this->assertEquals($expected, $menu->getItems());
    }

    public function testPathLinkCanBeAddedInConfig()
    {
        config(['hyde.navigation.custom' => [NavItem::forLink('foo', 'foo')]]);

        $menu = $this->createNavigationMenu();

        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::forLink('foo', 'foo'),
        ]);

        $this->assertCount(count($expected), $menu->getItems());
        $this->assertEquals($expected, $menu->getItems());
    }

    public function testDuplicatesAreRemovedWhenAddingInConfig()
    {
        config(['hyde.navigation.custom' => [
            NavItem::forLink('foo', 'foo'),
            NavItem::forLink('foo', 'foo'),
        ]]);

        $menu = $this->createNavigationMenu();

        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::forLink('foo', 'foo'),
        ]);

        $this->assertCount(count($expected), $menu->getItems());
        $this->assertEquals($expected, $menu->getItems());
    }

    public function testDuplicatesAreRemovedWhenAddingInConfigRegardlessOfDestination()
    {
        config(['hyde.navigation.custom' => [
            NavItem::forLink('foo', 'foo'),
            NavItem::forLink('bar', 'foo'),
        ]]);

        $menu = $this->createNavigationMenu();

        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::forLink('foo', 'foo'),
        ]);

        $this->assertCount(count($expected), $menu->getItems());
        $this->assertEquals($expected, $menu->getItems());
    }

    public function testConfigItemsTakePrecedenceOverGeneratedItems()
    {
        $this->file('_pages/foo.md');

        config(['hyde.navigation.custom' => [NavItem::forLink('bar', 'Foo')]]);

        $menu = $this->createNavigationMenu();

        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::forLink('bar', 'Foo'),
        ]);

        $this->assertCount(count($expected), $menu->getItems());
        $this->assertEquals($expected, $menu->getItems());
    }

    public function testDocumentationPagesThatAreNotIndexAreNotAddedToTheMenu()
    {
        $this->file('_docs/foo.md');
        $this->file('_docs/index.md');

        $menu = $this->createNavigationMenu();

        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::fromRoute(Routes::get('docs/index')),
        ]);

        $this->assertCount(count($expected), $menu->getItems());
        $this->assertEquals($expected, $menu->getItems());
    }

    public function testPagesInSubdirectoriesAreNotAddedToTheNavigationMenu()
    {
        $this->directory('_pages/foo');
        $this->file('_pages/foo/bar.md');

        $menu = $this->createNavigationMenu();
        $expected = collect([NavItem::fromRoute(Routes::get('index'))]);

        $this->assertCount(count($expected), $menu->getItems());
        $this->assertEquals($expected, $menu->getItems());
    }

    public function testPagesInSubdirectoriesCanBeAddedToTheNavigationMenuWithConfigFlatSetting()
    {
        config(['hyde.navigation.subdirectories' => 'flat']);
        $this->directory('_pages/foo');
        $this->file('_pages/foo/bar.md');

        $menu = $this->createNavigationMenu();
        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::fromRoute(Routes::get('foo/bar')),
        ]);

        $this->assertCount(count($expected), $menu->getItems());
        $this->assertEquals($expected, $menu->getItems());
    }

    public function testPagesInSubdirectoriesAreNotAddedToTheNavigationMenuWithConfigDropdownSetting()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);
        $this->directory('_pages/foo');
        $this->file('_pages/foo/bar.md');

        $menu = $this->createNavigationMenu();
        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::dropdown('foo', [
                NavItem::fromRoute(Routes::get('foo/bar')),
            ]),
        ]);

        $this->assertCount(count($expected), $menu->getItems());
        $this->assertEquals($expected, $menu->getItems());
    }

    public function testPagesInDropdownsDoNotGetAddedToTheMainNavigation()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);

        Routes::push((new MarkdownPage('foo'))->getRoute());
        Routes::push((new MarkdownPage('bar/baz'))->getRoute());
        $menu = $this->createNavigationMenu();

        $this->assertCount(3, $menu->getItems());
        $this->assertEquals([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::fromRoute((new MarkdownPage('foo'))->getRoute()),
            NavItem::dropdown('bar', [
                NavItem::fromRoute((new MarkdownPage('bar/baz'))->getRoute()),
            ]),
        ], $menu->getItems()->all());
    }

    public function testCanGetMenuFromServiceContainer()
    {
        $this->assertEquals($this->createNavigationMenu(), app('navigation')->getMenu('main'));
    }

    public function testCanAddItemsToMainNavigationMenuResolvedFromContainer()
    {
        $navigation = app('navigation')->getMenu('main');
        $navigation->add(new NavItem(new ExternalRoute('/foo'), 'Foo'));

        $this->assertCount(2, $navigation->getItems());
        $this->assertSame('Foo', $navigation->getItems()->last()->label);
    }

    protected function createNavigationMenu(): NavigationMenu
    {
        return GeneratesMainNavigationMenu::handle();
    }
}
