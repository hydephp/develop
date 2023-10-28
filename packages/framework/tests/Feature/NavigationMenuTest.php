<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use BadMethodCallException;
use Hyde\Support\Models\Route;

use function collect;
use function config;

use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Features\Navigation\DropdownNavItem;
use Hyde\Framework\Features\Navigation\NavigationMenu;
use Hyde\Framework\Features\Navigation\NavItem;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;

/**
 * @covers \Hyde\Framework\Features\Navigation\NavigationMenu
 * @covers \Hyde\Framework\Features\Navigation\BaseNavigationMenu
 */
class NavigationMenuTest extends TestCase
{
    public function test_constructor()
    {
        $this->assertInstanceOf(NavigationMenu::class, NavigationMenu::create());
    }

    public function test_generate_method_creates_collection_of_nav_items()
    {
        $this->assertInstanceOf(Collection::class, NavigationMenu::create()->items);
    }

    public function test_get_items_returns_items()
    {
        $this->assertEquals(NavigationMenu::create()->items, NavigationMenu::create()->getItems());
    }

    public function test_items_are_sorted_by_priority()
    {
        Routes::addRoute(new Route(new MarkdownPage('foo', ['navigation.priority' => 1])));
        Routes::addRoute(new Route(new MarkdownPage('bar', ['navigation.priority' => 2])));
        Routes::addRoute(new Route(new MarkdownPage('baz', ['navigation.priority' => 3])));

        $this->assertSame(['Home', 'Foo', 'Bar', 'Baz'], NavigationMenu::create()->items->pluck('label')->toArray());
    }

    public function test_items_with_hidden_property_set_to_true_are_not_added()
    {
        Routes::addRoute(new Route(new MarkdownPage('foo', ['navigation.hidden' => true])));
        Routes::addRoute(new Route(new MarkdownPage('bar', ['navigation.hidden' => false])));

        $this->assertSame(['Home', 'Bar'], NavigationMenu::create()->items->pluck('label')->toArray());
    }

    public function test_created_collection_is_sorted_by_navigation_menu_priority()
    {
        $this->file('_pages/foo.md');
        $this->file('_docs/index.md');

        $menu = NavigationMenu::create();

        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::fromRoute(Routes::get('foo')),
            NavItem::fromRoute(Routes::get('docs/index')),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function test_is_sorted_automatically_when_using_navigation_menu_create()
    {
        $this->file('_pages/foo.md');

        $menu = NavigationMenu::create();

        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::fromRoute(Routes::get('foo')),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function test_collection_only_contains_nav_items()
    {
        $this->assertContainsOnlyInstancesOf(NavItem::class, NavigationMenu::create()->items);
    }

    public function test_external_link_can_be_added_in_config()
    {
        config(['hyde.navigation.custom' => [NavItem::forLink('https://example.com', 'foo')]]);

        $menu = NavigationMenu::create();

        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::forLink('https://example.com', 'foo'),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function test_path_link_can_be_added_in_config()
    {
        config(['hyde.navigation.custom' => [NavItem::forLink('foo', 'foo')]]);

        $menu = NavigationMenu::create();

        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::forLink('foo', 'foo'),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function test_duplicates_are_removed_when_adding_in_config()
    {
        config(['hyde.navigation.custom' => [
            NavItem::forLink('foo', 'foo'),
            NavItem::forLink('foo', 'foo'),
        ]]);

        $menu = NavigationMenu::create();

        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::forLink('foo', 'foo'),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function test_duplicates_are_removed_when_adding_in_config_regardless_of_destination()
    {
        config(['hyde.navigation.custom' => [
            NavItem::forLink('foo', 'foo'),
            NavItem::forLink('bar', 'foo'),
        ]]);

        $menu = NavigationMenu::create();

        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::forLink('foo', 'foo'),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function test_config_items_take_precedence_over_generated_items()
    {
        $this->file('_pages/foo.md');

        config(['hyde.navigation.custom' => [NavItem::forLink('bar', 'Foo')]]);

        $menu = NavigationMenu::create();

        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::forLink('bar', 'Foo'),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function test_documentation_pages_that_are_not_index_are_not_added_to_the_menu()
    {
        $this->file('_docs/foo.md');
        $this->file('_docs/index.md');

        $menu = NavigationMenu::create();

        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::fromRoute(Routes::get('docs/index')),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function test_pages_in_subdirectories_are_not_added_to_the_navigation_menu()
    {
        $this->directory('_pages/foo');
        $this->file('_pages/foo/bar.md');

        $menu = NavigationMenu::create();
        $expected = collect([NavItem::fromRoute(Routes::get('index'))]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function test_pages_in_subdirectories_can_be_added_to_the_navigation_menu_with_config_flat_setting()
    {
        config(['hyde.navigation.subdirectories' => 'flat']);
        $this->directory('_pages/foo');
        $this->file('_pages/foo/bar.md');

        $menu = NavigationMenu::create();
        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::fromRoute(Routes::get('foo/bar')),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function test_pages_in_subdirectories_are_not_added_to_the_navigation_menu_with_config_dropdown_setting()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);
        $this->directory('_pages/foo');
        $this->file('_pages/foo/bar.md');

        $menu = NavigationMenu::create();
        $expected = collect([
            NavItem::fromRoute(Routes::get('index')),
            DropdownNavItem::fromArray('foo', [
                NavItem::fromRoute(Routes::get('foo/bar')),
            ]),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function test_has_dropdowns_returns_false_when_there_are_no_dropdowns()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);
        $menu = NavigationMenu::create();
        $this->assertFalse($menu->hasDropdowns());
    }

    public function test_has_dropdowns_returns_true_when_there_are_dropdowns()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);
        Routes::addRoute((new MarkdownPage('foo/bar'))->getRoute());
        $menu = NavigationMenu::create();
        $this->assertTrue($menu->hasDropdowns());
    }

    public function test_has_dropdowns_always_returns_false_when_dropdowns_are_disabled()
    {
        Routes::addRoute((new MarkdownPage('foo/bar'))->getRoute());
        $this->assertFalse(NavigationMenu::create()->hasDropdowns());
    }

    public function test_get_dropdowns_returns_empty_array_there_are_no_dropdowns()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);
        $menu = NavigationMenu::create();
        $this->assertCount(0, $menu->getDropdowns());
        $this->assertSame([], $menu->getDropdowns());
    }

    public function test_get_dropdowns_returns_correct_array_when_there_are_dropdowns()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);
        Routes::addRoute((new MarkdownPage('foo/bar'))->getRoute());
        $menu = NavigationMenu::create();
        $this->assertCount(1, $menu->getDropdowns());

        $this->assertEquals([
            DropdownNavItem::fromArray('foo', [
                NavItem::fromRoute((new MarkdownPage('foo/bar'))->getRoute()),
            ]), ], $menu->getDropdowns());
    }

    public function test_get_dropdowns_with_multiple_items()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);

        Routes::addRoute((new MarkdownPage('foo/bar'))->getRoute());
        Routes::addRoute((new MarkdownPage('foo/baz'))->getRoute());
        $menu = NavigationMenu::create();

        $this->assertCount(1, $menu->getDropdowns());

        $this->assertEquals([
            DropdownNavItem::fromArray('foo', [
                NavItem::fromRoute((new MarkdownPage('foo/bar'))->getRoute()),
                NavItem::fromRoute((new MarkdownPage('foo/baz'))->getRoute()),
            ]),
        ], $menu->getDropdowns());
    }

    public function test_get_dropdowns_with_multiple_dropdowns()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);

        Routes::addRoute((new MarkdownPage('foo/bar'))->getRoute());
        Routes::addRoute((new MarkdownPage('foo/baz'))->getRoute());
        Routes::addRoute((new MarkdownPage('cat/hat'))->getRoute());

        $menu = NavigationMenu::create();

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

    public function test_get_dropdowns_throws_exception_when_disabled()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Dropdowns are not enabled. Enable it by setting `hyde.navigation.subdirectories` to `dropdown`.');

        $menu = NavigationMenu::create();
        $menu->getDropdowns();
    }

    public function test_documentation_pages_do_not_get_added_to_dropdowns()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);

        Routes::addRoute((new DocumentationPage('foo'))->getRoute());
        Routes::addRoute((new DocumentationPage('bar/baz'))->getRoute());
        $menu = NavigationMenu::create();

        $this->assertFalse($menu->hasDropdowns());
        $this->assertCount(0, $menu->getDropdowns());
    }

    public function test_blog_posts_do_not_get_added_to_dropdowns()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);

        Routes::addRoute((new MarkdownPost('foo'))->getRoute());
        Routes::addRoute((new MarkdownPost('bar/baz'))->getRoute());

        $menu = NavigationMenu::create();
        $this->assertFalse($menu->hasDropdowns());
        $this->assertCount(0, $menu->getDropdowns());
    }

    public function test_pages_in_dropdowns_do_not_get_added_to_the_main_navigation()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);

        Routes::push((new MarkdownPage('foo'))->getRoute());
        Routes::push((new MarkdownPage('bar/baz'))->getRoute());
        $menu = NavigationMenu::create();

        $this->assertCount(3, $menu->items);
        $this->assertEquals([
            NavItem::fromRoute(Routes::get('index')),
            NavItem::fromRoute((new MarkdownPage('foo'))->getRoute()),
            DropdownNavItem::fromArray('bar', [
                NavItem::fromRoute((new MarkdownPage('bar/baz'))->getRoute()),
            ]),
        ], $menu->items->all());
    }

    public function test_dropdown_menu_items_are_sorted_by_priority()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);

        Routes::addRoute(new Route(new MarkdownPage('foo/foo', ['navigation.priority' => 1])));
        Routes::addRoute(new Route(new MarkdownPage('foo/bar', ['navigation.priority' => 2])));
        Routes::addRoute(new Route(new MarkdownPage('foo/baz', ['navigation.priority' => 3])));

        $menu = NavigationMenu::create();
        $dropdowns = $menu->getDropdowns();

        $this->assertSame(['Foo', 'Bar', 'Baz'], $dropdowns[0]->getItems()->pluck('label')->toArray());
    }
}
