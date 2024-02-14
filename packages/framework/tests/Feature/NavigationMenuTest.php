<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use BadMethodCallException;
use Hyde\Support\Models\Route;
use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Features\Navigation\NavigationMenu;
use Hyde\Framework\Features\Navigation\DropdownNavItem;
use Hyde\Framework\Features\Navigation\NavItem;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;
use Hyde\Framework\Features\Navigation\GeneratesMainNavigationMenu;

/**
 * @covers \Hyde\Framework\Features\Navigation\GeneratesMainNavigationMenu
 * @covers \Hyde\Framework\Features\Navigation\BaseNavigationMenu
 * @covers \Hyde\Framework\Features\Navigation\NavigationMenu
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
            DropdownNavItem::fromArray('foo', [
                NavItem::fromRoute(Routes::get('foo/bar')),
            ]),
        ]);

        $this->assertCount(count($expected), $menu->getItems());
        $this->assertEquals($expected, $menu->getItems());
    }

    public function testHasDropdownsReturnsFalseWhenThereAreNoDropdowns()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);
        $menu = $this->createNavigationMenu();
        $this->assertFalse($menu->hasDropdowns());
    }

    public function testHasDropdownsReturnsTrueWhenThereAreDropdowns()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);
        Routes::addRoute((new MarkdownPage('foo/bar'))->getRoute());
        $menu = $this->createNavigationMenu();
        $this->assertTrue($menu->hasDropdowns());
    }

    public function testHasDropdownsAlwaysReturnsFalseWhenDropdownsAreDisabled()
    {
        Routes::addRoute((new MarkdownPage('foo/bar'))->getRoute());
        $this->assertFalse($this->createNavigationMenu()->hasDropdowns());
    }

    public function testGetDropdownsReturnsEmptyArrayThereAreNoDropdowns()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);
        $menu = $this->createNavigationMenu();
        $this->assertCount(0, $menu->getDropdowns());
        $this->assertSame([], $menu->getDropdowns());
    }

    public function testGetDropdownsReturnsCorrectArrayWhenThereAreDropdowns()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);
        Routes::addRoute((new MarkdownPage('foo/bar'))->getRoute());
        $menu = $this->createNavigationMenu();
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
        $menu = $this->createNavigationMenu();

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

        $menu = $this->createNavigationMenu();

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

        $menu = $this->createNavigationMenu();
        $menu->getDropdowns();
    }

    public function testDocumentationPagesDoNotGetAddedToDropdowns()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);

        Routes::addRoute((new DocumentationPage('foo'))->getRoute());
        Routes::addRoute((new DocumentationPage('bar/baz'))->getRoute());
        $menu = $this->createNavigationMenu();

        $this->assertFalse($menu->hasDropdowns());
        $this->assertCount(0, $menu->getDropdowns());
    }

    public function testBlogPostsDoNotGetAddedToDropdowns()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);

        Routes::addRoute((new MarkdownPost('foo'))->getRoute());
        Routes::addRoute((new MarkdownPost('bar/baz'))->getRoute());

        $menu = $this->createNavigationMenu();
        $this->assertFalse($menu->hasDropdowns());
        $this->assertCount(0, $menu->getDropdowns());
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
            DropdownNavItem::fromArray('bar', [
                NavItem::fromRoute((new MarkdownPage('bar/baz'))->getRoute()),
            ]),
        ], $menu->getItems()->all());
    }

    public function testDropdownMenuItemsAreSortedByPriority()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);

        Routes::addRoute(new Route(new MarkdownPage('foo/foo', ['navigation.priority' => 1])));
        Routes::addRoute(new Route(new MarkdownPage('foo/bar', ['navigation.priority' => 2])));
        Routes::addRoute(new Route(new MarkdownPage('foo/baz', ['navigation.priority' => 3])));

        $menu = $this->createNavigationMenu();
        $dropdowns = $menu->getDropdowns();

        $this->assertSame(['Foo', 'Bar', 'Baz'], $dropdowns[0]->getItems()->pluck('label')->toArray());
    }

    protected function createNavigationMenu(): NavigationMenu
    {
        return GeneratesMainNavigationMenu::handle();
    }
}
