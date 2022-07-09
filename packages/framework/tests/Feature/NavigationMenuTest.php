<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Hyde;
use Hyde\Framework\Models\NavItem;
use Hyde\Framework\Modules\Navigation\NavigationMenu;
use Hyde\Framework\Modules\Routing\Route;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;

/**
 * @covers \Hyde\Framework\Modules\Navigation\NavigationMenu
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
        $menu = new NavigationMenu();

        $this->assertEquals('index.html', $menu->getHomeLink(''));
    }

    public function test_set_current_route()
    {
        $menu = new NavigationMenu();

        $this->assertFalse(isset($menu->currentRoute));
        $menu->setCurrentRoute(Route::get('index'));
        $this->assertTrue(isset($menu->currentRoute));
        $this->assertInstanceOf(Route::class, $menu->currentRoute);
        $this->assertEquals('index', $menu->currentRoute->getRouteKey());
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
            NavItem::toRoute(Route::get('404')),
            NavItem::toRoute(Route::get('index')),
        ]);

        $this->assertEquals($expected, $menu->items);
    }

    public function test_sort_method_sorts_items_by_priority()
    {
        $menu = new NavigationMenu();
        $menu->generate()->sort();

        $expected = collect([
            NavItem::toRoute(Route::get('index')),
            NavItem::toRoute(Route::get('404')),
        ]);

        $this->assertEquals($expected, $menu->items);
    }

    public function test_filter_method_removes_items_with_hidden_property_set_to_true()
    {
        $menu = new NavigationMenu();
        $menu->generate()->filter();

        $expected = collect([
            NavItem::toRoute(Route::get('index')),
        ]);

        $this->assertEquals($expected, $menu->items);
    }

    public function test_static_create_method_creates_new_processed_collection()
    {
        Hyde::touch('_pages/foo.md');
        $menu = NavigationMenu::create(Route::get('index'));

        $this->assertInstanceOf(NavigationMenu::class, $menu);
        $this->assertEquals(
            (new NavigationMenu())->setCurrentRoute(Route::get('index'))->generate()->filter()->sort(),
            NavigationMenu::create(Route::get('index'))
        );
    }

    public function test_created_collection_is_sorted_by_navigation_menu_priority()
    {
        Hyde::touch('_pages/foo.md');
        Hyde::touch('_docs/index.md');

        $menu = NavigationMenu::create(Route::get('index'));

        $expected = collect([
            NavItem::toRoute(Route::get('index')),
            NavItem::toRoute(Route::get('docs/index')),
            NavItem::toRoute(Route::get('foo')),
        ]);

        $this->assertEquals($expected, $menu->items);

        Hyde::unlink('_pages/foo.md');
        Hyde::unlink('_docs/index.md');
    }

    public function test_is_sorted_automatically_when_using_navigation_menu_create()
    {
        Hyde::touch('_pages/foo.md');

        $menu = NavigationMenu::create(Route::get('index'));

        $expected = collect([
            NavItem::toRoute(Route::get('index')),
            NavItem::toRoute(Route::get('foo')),
        ]);

        $this->assertEquals($expected, $menu->items);

        Hyde::unlink('_pages/foo.md');
    }

    public function test_collection_only_contains_nav_items()
    {
        $this->assertContainsOnlyInstancesOf(NavItem::class, NavigationMenu::create(Route::get('index'))->items);
    }

    // test external link can be added in config
    public function test_external_link_can_be_added_in_config()
    {
        config(['hyde.navigation.custom' => [NavItem::toLink('https://example.com', 'foo')]]);

        $menu = NavigationMenu::create(Route::get('index'));

        $expected = collect([
            NavItem::toRoute(Route::get('index')),
            NavItem::toLink('https://example.com', 'foo'),
        ]);

        $this->assertEquals($expected, $menu->items);
    }

    // test path link can be added in config
    public function test_path_link_can_be_added_in_config()
    {
        config(['hyde.navigation.custom' => [NavItem::toLink('foo', 'foo')]]);

        $menu = NavigationMenu::create(Route::get('index'));

        $expected = collect([
            NavItem::toRoute(Route::get('index')),
            NavItem::toLink('foo', 'foo'),
        ]);

        $this->assertEquals($expected, $menu->items);
    }

    // test route link can be added in config (with route key)
    public function test_route_link_can_be_added_in_config_with_route_key()
    {
        Hyde::touch('_pages/foo.md');
        config(['hyde.navigation.custom' => [NavItem::toRoute('foo')]]);

        $menu = NavigationMenu::create(Route::get('index'));

        $expected = collect([
            NavItem::toRoute(Route::get('index')),
            NavItem::toRoute(Route::get('foo')),
        ]);

        $this->assertEquals($expected, $menu->items);

        Hyde::unlink('_pages/foo.md');
    }

    // test route link can be added in config (with route object)
    public function test_route_link_can_be_added_in_config_with_route_object()
    {
        Hyde::touch('_pages/foo.md');
        config(['hyde.navigation.custom' => [NavItem::toRoute(Route::get('foo'))]]);

        $menu = NavigationMenu::create(Route::get('index'));

        $expected = collect([
            NavItem::toRoute(Route::get('index')),
            NavItem::toRoute(Route::get('foo')),
        ]);

        $this->assertEquals($expected, $menu->items);

        Hyde::unlink('_pages/foo.md');
    }

    // test duplicates are removed when adding in config
    public function test_duplicates_are_removed_when_adding_in_config()
    {
        config(['hyde.navigation.custom' => [
            NavItem::toRoute(Route::get('index')),
            NavItem::toRoute(Route::get('index')),
        ]]);

        $menu = NavigationMenu::create(Route::get('index'));

        $expected = collect([
            NavItem::toRoute(Route::get('index')),
        ]);

        $this->assertEquals($expected, $menu->items);
    }

    // test duplicates are removed when adding in config regardless of label
    public function test_duplicates_are_removed_when_adding_in_config_regardless_of_label()
    {
        config(['hyde.navigation.custom' => [
            NavItem::toRoute(Route::get('index'), 'foo'),
            NavItem::toRoute(Route::get('index'), 'bar'),
        ]]);

        $menu = NavigationMenu::create(Route::get('index'));

        $expected = collect([
            NavItem::toRoute(Route::get('index')),
        ]);

        $this->assertEquals($expected, $menu->items);
    }

}
