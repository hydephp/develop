<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Support\Models\Route;
use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Features\Navigation\NavigationGroup;
use Hyde\Framework\Features\Navigation\MainNavigationMenu;
use Hyde\Framework\Features\Navigation\NavigationItem;
use Hyde\Pages\MarkdownPage;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;
use Hyde\Framework\Features\Navigation\NavigationMenuGenerator;

/**
 * @covers \Hyde\Framework\Features\Navigation\NavigationMenu
 * @covers \Hyde\Framework\Features\Navigation\MainNavigationMenu
 * @covers \Hyde\Framework\Features\Navigation\NavigationMenuGenerator
 *
 * @see \Hyde\Framework\Testing\Unit\NavigationMenuUnitTest
 */
class NavigationMenuTest extends TestCase
{
    public function testConstructor()
    {
        $this->assertInstanceOf(MainNavigationMenu::class, $this->createNavigationMenu());
    }

    public function testGenerateMethodCreatesCollectionOfNavigationItems()
    {
        $this->assertInstanceOf(Collection::class, $this->createNavigationMenu()->getItems());
        $this->assertContainsOnlyInstancesOf(NavigationItem::class, $this->createNavigationMenu()->getItems());
    }

    public function testGetItemsReturnsItems()
    {
        $this->assertEquals(collect([
            NavigationItem::create(Routes::get('index')),
        ]), $this->createNavigationMenu()->getItems());
    }

    public function testItemsAreSortedByPriority()
    {
        Routes::addRoute(new Route(new MarkdownPage('foo', ['navigation.priority' => 1])));
        Routes::addRoute(new Route(new MarkdownPage('bar', ['navigation.priority' => 2])));
        Routes::addRoute(new Route(new MarkdownPage('baz', ['navigation.priority' => 3])));

        $this->assertSame(['Home', 'Foo', 'Bar', 'Baz'], $this->createNavigationMenu()->getItems()->map(fn ($item) => $item->getLabel())->toArray());
    }

    public function testItemsWithHiddenPropertySetToTrueAreNotAdded()
    {
        Routes::addRoute(new Route(new MarkdownPage('foo', ['navigation.hidden' => true])));
        Routes::addRoute(new Route(new MarkdownPage('bar', ['navigation.hidden' => false])));

        $this->assertSame(['Home', 'Bar'], $this->createNavigationMenu()->getItems()->map(fn ($item) => $item->getLabel())->toArray());
    }

    public function testCreatedCollectionIsSortedByNavigationMenuPriority()
    {
        $this->file('_pages/foo.md');
        $this->file('_docs/index.md');

        $menu = $this->createNavigationMenu();

        $expected = collect([
            NavigationItem::create(Routes::get('index')),
            NavigationItem::create(Routes::get('foo')),
            NavigationItem::create(Routes::get('docs/index')),
        ]);

        $this->assertCount(count($expected), $menu->getItems());
        $this->assertEquals($expected, $menu->getItems());
    }

    public function testIsSortedAutomaticallyWhenUsingNavigationMenuCreate()
    {
        $this->file('_pages/foo.md');

        $menu = $this->createNavigationMenu();

        $expected = collect([
            NavigationItem::create(Routes::get('index')),
            NavigationItem::create(Routes::get('foo')),
        ]);

        $this->assertCount(count($expected), $menu->getItems());
        $this->assertEquals($expected, $menu->getItems());
    }

    public function testExternalLinkCanBeAddedInConfig()
    {
        config(['hyde.navigation.custom' => [NavigationItem::create('https://example.com', 'foo')]]);

        $menu = $this->createNavigationMenu();

        $expected = collect([
            NavigationItem::create(Routes::get('index')),
            NavigationItem::create('https://example.com', 'foo'),
        ]);

        $this->assertCount(count($expected), $menu->getItems());
        $this->assertEquals($expected, $menu->getItems());
    }

    public function testPathLinkCanBeAddedInConfig()
    {
        config(['hyde.navigation.custom' => [NavigationItem::create('foo', 'foo')]]);

        $menu = $this->createNavigationMenu();

        $expected = collect([
            NavigationItem::create(Routes::get('index')),
            NavigationItem::create('foo', 'foo'),
        ]);

        $this->assertCount(count($expected), $menu->getItems());
        $this->assertEquals($expected, $menu->getItems());
    }

    public function testDuplicatesAreNotRemovedWhenAddingInConfig()
    {
        config(['hyde.navigation.custom' => [
            NavigationItem::create('foo', 'foo'),
            NavigationItem::create('foo', 'foo'),
        ]]);

        $menu = $this->createNavigationMenu();

        $expected = collect([
            NavigationItem::create(Routes::get('index')),
            NavigationItem::create('foo', 'foo'),
            NavigationItem::create('foo', 'foo'),
        ]);

        $this->assertCount(count($expected), $menu->getItems());
        $this->assertEquals($expected, $menu->getItems());
    }

    public function testDuplicatesAreNotRemovedWhenAddingInConfigRegardlessOfDestination()
    {
        config(['hyde.navigation.custom' => [
            NavigationItem::create('foo', 'foo'),
            NavigationItem::create('bar', 'foo'),
        ]]);

        $menu = $this->createNavigationMenu();

        $expected = collect([
            NavigationItem::create(Routes::get('index')),
            NavigationItem::create('foo', 'foo'),
            NavigationItem::create('bar', 'foo'),
        ]);

        $this->assertCount(count($expected), $menu->getItems());
        $this->assertEquals($expected, $menu->getItems());
    }

    public function testConfigItemsTakePrecedenceOverGeneratedItems()
    {
        $this->file('_pages/foo.md');

        config(['hyde.navigation.custom' => [NavigationItem::create('bar', 'Foo')]]);

        $menu = $this->createNavigationMenu();

        $expected = collect([
            NavigationItem::create(Routes::get('index')),
            NavigationItem::create('bar', 'Foo'),
            NavigationItem::create(Routes::get('foo')),
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
            NavigationItem::create(Routes::get('index')),
            NavigationItem::create(Routes::get('docs/index')),
        ]);

        $this->assertCount(count($expected), $menu->getItems());
        $this->assertEquals($expected, $menu->getItems());
    }

    public function testPagesInSubdirectoriesAreNotAddedToTheNavigationMenu()
    {
        $this->directory('_pages/foo');
        $this->file('_pages/foo/bar.md');

        $menu = $this->createNavigationMenu();

        $expected = collect([NavigationItem::create(Routes::get('index'))]);

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
            NavigationItem::create(Routes::get('index')),
            NavigationItem::create(Routes::get('foo/bar')),
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
            NavigationItem::create(Routes::get('index')),
            NavigationGroup::create('Foo', [
                NavigationItem::create(Routes::get('foo/bar')),
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
            NavigationItem::create(Routes::get('index')),
            NavigationItem::create((new MarkdownPage('foo'))->getRoute()),
            NavigationGroup::create('Bar', [
                NavigationItem::create((new MarkdownPage('bar/baz'))->getRoute()),
            ]),
        ], $menu->getItems()->all());
    }

    public function testCanGetMenuFromServiceContainer()
    {
        $this->assertEquals($this->createNavigationMenu(), app('navigation.main'));
    }

    public function testCanAddItemsToMainNavigationMenuResolvedFromContainer()
    {
        Hyde::boot();

        $navigation = app('navigation.main');
        $navigation->add(new NavigationItem('/foo', 'Foo'));

        $this->assertCount(2, $navigation->getItems());
        $this->assertSame('Foo', $navigation->getItems()->last()->getLabel());
    }

    protected function createNavigationMenu(): MainNavigationMenu
    {
        return NavigationMenuGenerator::handle(MainNavigationMenu::class);
    }

    public function testHasDropdownsReturnsTrueWhenGroupIsExplicitlySetInFrontMatter()
    {
        config(['hyde.navigation.subdirectories' => 'hidden']);

        Routes::addRoute((new MarkdownPage('foo', matter: ['navigation.group' => 'test-group']))->getRoute());

        $this->assertTrue(NavigationMenu::create()->hasDropdowns());
    }

    public function testGetDropdownsReturnsCorrectArrayWhenGroupIsExplicitlySetInFrontMatter()
    {
        config(['hyde.navigation.subdirectories' => 'hidden']);

        Routes::addRoute((new MarkdownPage('foo', matter: ['navigation.group' => 'test-group']))->getRoute());

        $menu = NavigationMenu::create();
        $this->assertCount(1, $menu->getDropdowns());

        $this->assertEquals([
            DropdownNavItem::fromArray('test-group', [
                NavItem::fromRoute((new MarkdownPage('foo'))->getRoute()),
            ]),
        ], $menu->getDropdowns());
    }

    public function testHasDropdownsReturnsFalseWhenGroupIsNotExplicitlySetInFrontMatter()
    {
        config(['hyde.navigation.subdirectories' => 'hidden']);

        Routes::addRoute((new MarkdownPage('foo'))->getRoute());
        $menu = NavigationMenu::create();
        $this->assertFalse($menu->hasDropdowns());
    }
}
