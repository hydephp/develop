<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Navigation\NavigationMenu;
use Hyde\Framework\Features\Navigation\NavItem;
use Hyde\Hyde;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;

/**
 * @covers \Hyde\Framework\Features\Navigation\NavigationMenu
 */
class NavigationMenuTest extends TestCase
{
    public function test_constructor()
    {
        $menu = new NavigationMenu();

        $this->assertInstanceOf(NavigationMenu::class, $menu);
    }

    public function test_home_route()
    {
        $this->assertEquals('index.html', Route::home());
    }

    public function test_generate_method_creates_collection_of_nav_items()
    {
        $menu = new NavigationMenu();

        $this->assertInstanceOf(Collection::class, $menu->items);
        $this->assertEmpty($menu->items);
    }

    public function test_generate_method_adds_route_items()
    {
        $menu = new NavigationMenu();
        $menu->generate();

        $expected = collect([
            NavItem::fromRoute(Route::get('404')),
            NavItem::fromRoute(Route::get('index')),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function test_sort_method_sorts_items_by_priority()
    {
        $menu = new NavigationMenu();
        $menu->generate()->sort();

        $expected = collect([
            NavItem::fromRoute(Route::get('index')),
            NavItem::fromRoute(Route::get('404')),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function test_filter_method_removes_items_with_hidden_property_set_to_true()
    {
        $menu = new NavigationMenu();
        $menu->generate()->filter();

        $expected = collect([
            NavItem::fromRoute(Route::get('index')),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function test_static_create_method_creates_new_processed_collection()
    {
        Hyde::touch('_pages/foo.md');
        $menu = NavigationMenu::create();

        $this->assertInstanceOf(NavigationMenu::class, $menu);
        $this->assertEquals(
            (new NavigationMenu())->generate()->filter()->sort(),
            NavigationMenu::create()
        );
    }

    public function test_created_collection_is_sorted_by_navigation_menu_priority()
    {
        Hyde::touch('_pages/foo.md');
        Hyde::touch('_docs/index.md');

        $menu = NavigationMenu::create();

        $expected = collect([
            NavItem::fromRoute(Route::get('index')),
            NavItem::fromRoute(Route::get('foo')),
            NavItem::fromRoute(Route::get('docs/index')),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);

        Hyde::unlink('_pages/foo.md');
        Hyde::unlink('_docs/index.md');
    }

    public function test_is_sorted_automatically_when_using_navigation_menu_create()
    {
        Hyde::touch('_pages/foo.md');

        $menu = NavigationMenu::create();

        $expected = collect([
            NavItem::fromRoute(Route::get('index')),
            NavItem::fromRoute(Route::get('foo')),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);

        Hyde::unlink('_pages/foo.md');
    }

    public function test_collection_only_contains_nav_items()
    {
        $this->assertContainsOnlyInstancesOf(NavItem::class, NavigationMenu::create()->items);
    }

    public function test_external_link_can_be_added_in_config()
    {
        config(['hyde.navigation.custom' => [NavItem::toLink('https://example.com', 'foo')]]);

        $menu = NavigationMenu::create();

        $expected = collect([
            NavItem::fromRoute(Route::get('index')),
            NavItem::toLink('https://example.com', 'foo'),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function test_path_link_can_be_added_in_config()
    {
        config(['hyde.navigation.custom' => [NavItem::toLink('foo', 'foo')]]);

        $menu = NavigationMenu::create();

        $expected = collect([
            NavItem::fromRoute(Route::get('index')),
            NavItem::toLink('foo', 'foo'),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function test_duplicates_are_removed_when_adding_in_config()
    {
        config(['hyde.navigation.custom' => [
            NavItem::toLink('foo', 'foo'),
            NavItem::toLink('foo', 'foo'),
        ]]);

        $menu = NavigationMenu::create();

        $expected = collect([
            NavItem::fromRoute(Route::get('index')),
            NavItem::toLink('foo', 'foo'),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function test_duplicates_are_removed_when_adding_in_config_regardless_of_label()
    {
        config(['hyde.navigation.custom' => [
            NavItem::toLink('foo', 'foo'),
            NavItem::toLink('foo', 'bar'),
        ]]);

        $menu = NavigationMenu::create();

        $expected = collect([
            NavItem::fromRoute(Route::get('index')),
            NavItem::toLink('foo', 'foo'),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function test_documentation_pages_that_are_not_index_are_not_added_to_the_menu()
    {
        Hyde::touch('_docs/foo.md');
        Hyde::touch('_docs/index.md');

        $menu = NavigationMenu::create();

        $expected = collect([
            NavItem::fromRoute(Route::get('index')),
            NavItem::fromRoute(Route::get('docs/index')),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);

        Hyde::unlink('_docs/foo.md');
        Hyde::unlink('_docs/index.md');
    }
}
