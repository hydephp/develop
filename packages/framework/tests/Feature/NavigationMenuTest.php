<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use BadMethodCallException;
use Hyde\Support\Models\Route;
use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Features\Navigation\DropdownNavItem;
use Hyde\Framework\Features\Navigation\MainNavigationMenu;
use Hyde\Framework\Features\Navigation\NavItem;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;

/**
 * @covers \Hyde\Framework\Features\Navigation\MainNavigationMenu
 * @covers \Hyde\Framework\Features\Navigation\BaseNavigationMenu
 * @covers \Hyde\Framework\Features\Navigation\NavigationMenu
 */
class NavigationMenuTest extends TestCase
{
    public function testConstructor()
    {
        $this->assertInstanceOf(MainNavigationMenu::class, MainNavigationMenu::create());
    }

    public function testGenerateMethodCreatesCollectionOfNavItems()
    {
        $this->assertInstanceOf(Collection::class, MainNavigationMenu::create()->items);
    }

    public function testGetItemsReturnsItems()
    {
        $this->assertEquals(MainNavigationMenu::create()->items, MainNavigationMenu::create()->getItems());
    }

    public function testItemsAreSortedByPriority()
    {
        Routes::addRoute(new Route(new MarkdownPage('foo', ['navigation.priority' => 1])));
        Routes::addRoute(new Route(new MarkdownPage('bar', ['navigation.priority' => 2])));
        Routes::addRoute(new Route(new MarkdownPage('baz', ['navigation.priority' => 3])));

        $this->assertSame(['Home', 'Foo', 'Bar', 'Baz'], MainNavigationMenu::create()->items->pluck('label')->toArray());
    }

    public function testItemsWithHiddenPropertySetToTrueAreNotAdded()
    {
        Routes::addRoute(new Route(new MarkdownPage('foo', ['navigation.hidden' => true])));
        Routes::addRoute(new Route(new MarkdownPage('bar', ['navigation.hidden' => false])));

        $this->assertSame(['Home', 'Bar'], MainNavigationMenu::create()->items->pluck('label')->toArray());
    }

    public function testCreatedCollectionIsSortedByNavigationMenuPriority()
    {
        $this->file('_pages/foo.md');
        $this->file('_docs/index.md');

        $menu = MainNavigationMenu::create();

        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::fromRoute(Routes::get('foo')),
            NavItem::fromRoute(Routes::get('docs/index')),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function testIsSortedAutomaticallyWhenUsingNavigationMenuCreate()
    {
        $this->file('_pages/foo.md');

        $menu = MainNavigationMenu::create();

        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::fromRoute(Routes::get('foo')),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function testCollectionOnlyContainsNavItems()
    {
        $this->assertContainsOnlyInstancesOf(NavItem::class, MainNavigationMenu::create()->items);
    }

    public function testExternalLinkCanBeAddedInConfig()
    {
        config(['hyde.navigation.custom' => [NavItem::forLink('https://example.com', 'foo')]]);

        $menu = MainNavigationMenu::create();

        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::forLink('https://example.com', 'foo'),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function testPathLinkCanBeAddedInConfig()
    {
        config(['hyde.navigation.custom' => [NavItem::forLink('foo', 'foo')]]);

        $menu = MainNavigationMenu::create();

        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::forLink('foo', 'foo'),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function testDuplicatesAreRemovedWhenAddingInConfig()
    {
        config(['hyde.navigation.custom' => [
            NavItem::forLink('foo', 'foo'),
            NavItem::forLink('foo', 'foo'),
        ]]);

        $menu = MainNavigationMenu::create();

        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::forLink('foo', 'foo'),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function testDuplicatesAreRemovedWhenAddingInConfigRegardlessOfDestination()
    {
        config(['hyde.navigation.custom' => [
            NavItem::forLink('foo', 'foo'),
            NavItem::forLink('bar', 'foo'),
        ]]);

        $menu = MainNavigationMenu::create();

        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::forLink('foo', 'foo'),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function testConfigItemsTakePrecedenceOverGeneratedItems()
    {
        $this->file('_pages/foo.md');

        config(['hyde.navigation.custom' => [NavItem::forLink('bar', 'Foo')]]);

        $menu = MainNavigationMenu::create();

        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::forLink('bar', 'Foo'),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function testDocumentationPagesThatAreNotIndexAreNotAddedToTheMenu()
    {
        $this->file('_docs/foo.md');
        $this->file('_docs/index.md');

        $menu = MainNavigationMenu::create();

        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::fromRoute(Routes::get('docs/index')),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function testPagesInSubdirectoriesAreNotAddedToTheNavigationMenu()
    {
        $this->directory('_pages/foo');
        $this->file('_pages/foo/bar.md');

        $menu = MainNavigationMenu::create();
        $expected = collect([NavItem::fromRoute(Routes::get('index'))]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function testPagesInSubdirectoriesCanBeAddedToTheNavigationMenuWithConfigFlatSetting()
    {
        config(['hyde.navigation.subdirectories' => 'flat']);
        $this->directory('_pages/foo');
        $this->file('_pages/foo/bar.md');

        $menu = MainNavigationMenu::create();
        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::fromRoute(Routes::get('foo/bar')),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function testPagesInSubdirectoriesAreNotAddedToTheNavigationMenuWithConfigDropdownSetting()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);
        $this->directory('_pages/foo');
        $this->file('_pages/foo/bar.md');

        $menu = MainNavigationMenu::create();
        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            DropdownNavItem::fromArray('foo', [
                NavItem::fromRoute(Routes::get('foo/bar')),
            ]),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function testHasDropdownsReturnsFalseWhenThereAreNoDropdowns()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);
        $menu = MainNavigationMenu::create();
        $this->assertFalse($menu->hasDropdowns());
    }

    public function testHasDropdownsReturnsTrueWhenThereAreDropdowns()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);
        Routes::addRoute((new MarkdownPage('foo/bar'))->getRoute());
        $menu = MainNavigationMenu::create();
        $this->assertTrue($menu->hasDropdowns());
    }

    public function testHasDropdownsAlwaysReturnsFalseWhenDropdownsAreDisabled()
    {
        Routes::addRoute((new MarkdownPage('foo/bar'))->getRoute());
        $this->assertFalse(MainNavigationMenu::create()->hasDropdowns());
    }

    public function testGetDropdownsReturnsEmptyArrayThereAreNoDropdowns()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);
        $menu = MainNavigationMenu::create();
        $this->assertCount(0, $menu->getDropdowns());
        $this->assertSame([], $menu->getDropdowns());
    }

    public function testGetDropdownsReturnsCorrectArrayWhenThereAreDropdowns()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);
        Routes::addRoute((new MarkdownPage('foo/bar'))->getRoute());
        $menu = MainNavigationMenu::create();
        $this->assertCount(1, $menu->getDropdowns());

        $this->assertEquals([
            DropdownNavItem::fromArray('foo', [
                NavItem::fromRoute((new MarkdownPage('foo/bar'))->getRoute()),
            ]), ], $menu->getDropdowns());
    }

    public function testGetDropdownsWithMultipleItems()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);

        Routes::addRoute((new MarkdownPage('foo/bar'))->getRoute());
        Routes::addRoute((new MarkdownPage('foo/baz'))->getRoute());
        $menu = MainNavigationMenu::create();

        $this->assertCount(1, $menu->getDropdowns());

        $this->assertEquals([
            DropdownNavItem::fromArray('foo', [
                NavItem::fromRoute((new MarkdownPage('foo/bar'))->getRoute()),
                NavItem::fromRoute((new MarkdownPage('foo/baz'))->getRoute()),
            ]),
        ], $menu->getDropdowns());
    }

    public function testGetDropdownsWithMultipleDropdowns()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);

        Routes::addRoute((new MarkdownPage('foo/bar'))->getRoute());
        Routes::addRoute((new MarkdownPage('foo/baz'))->getRoute());
        Routes::addRoute((new MarkdownPage('cat/hat'))->getRoute());

        $menu = MainNavigationMenu::create();

        $this->assertCount(2, $menu->getDropdowns());

        $this->assertEquals([
            DropdownNavItem::fromArray('foo', [
                NavItem::fromRoute((new MarkdownPage('foo/bar'))->getRoute()),
                NavItem::fromRoute((new MarkdownPage('foo/baz'))->getRoute()),
            ]),
            DropdownNavItem::fromArray('cat', [
                NavItem::fromRoute((new MarkdownPage('cat/hat'))->getRoute()),
            ]),
        ], $menu->getDropdowns());
    }

    public function testGetDropdownsThrowsExceptionWhenDisabled()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Dropdowns are not enabled. Enable it by setting `hyde.navigation.subdirectories` to `dropdown`.');

        $menu = MainNavigationMenu::create();
        $menu->getDropdowns();
    }

    public function testDocumentationPagesDoNotGetAddedToDropdowns()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);

        Routes::addRoute((new DocumentationPage('foo'))->getRoute());
        Routes::addRoute((new DocumentationPage('bar/baz'))->getRoute());
        $menu = MainNavigationMenu::create();

        $this->assertFalse($menu->hasDropdowns());
        $this->assertCount(0, $menu->getDropdowns());
    }

    public function testBlogPostsDoNotGetAddedToDropdowns()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);

        Routes::addRoute((new MarkdownPost('foo'))->getRoute());
        Routes::addRoute((new MarkdownPost('bar/baz'))->getRoute());

        $menu = MainNavigationMenu::create();
        $this->assertFalse($menu->hasDropdowns());
        $this->assertCount(0, $menu->getDropdowns());
    }

    public function testPagesInDropdownsDoNotGetAddedToTheMainNavigation()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);

        Routes::push((new MarkdownPage('foo'))->getRoute());
        Routes::push((new MarkdownPage('bar/baz'))->getRoute());
        $menu = MainNavigationMenu::create();

        $this->assertCount(3, $menu->items);
        $this->assertEquals([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::fromRoute((new MarkdownPage('foo'))->getRoute()),
            DropdownNavItem::fromArray('bar', [
                NavItem::fromRoute((new MarkdownPage('bar/baz'))->getRoute()),
            ]),
        ], $menu->items->all());
    }

    public function testDropdownMenuItemsAreSortedByPriority()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);

        Routes::addRoute(new Route(new MarkdownPage('foo/foo', ['navigation.priority' => 1])));
        Routes::addRoute(new Route(new MarkdownPage('foo/bar', ['navigation.priority' => 2])));
        Routes::addRoute(new Route(new MarkdownPage('foo/baz', ['navigation.priority' => 3])));

        $menu = MainNavigationMenu::create();
        $dropdowns = $menu->getDropdowns();

        $this->assertSame(['Foo', 'Bar', 'Baz'], $dropdowns[0]->getItems()->pluck('label')->toArray());
    }
}
