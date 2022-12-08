<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use BadMethodCallException;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPost;
use function config;
use Hyde\Framework\Features\Navigation\NavigationMenu;
use Hyde\Framework\Features\Navigation\NavItem;
use Hyde\Hyde;
use Hyde\Pages\MarkdownPage;
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

    public function test_pages_in_subdirectories_are_not_added_to_the_navigation_menu()
    {
        $this->directory('_pages/foo');
        Hyde::touch('_pages/foo/bar.md');

        $menu = NavigationMenu::create();
        $expected = collect([NavItem::fromRoute(Route::get('index'))]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function test_pages_in_subdirectories_can_be_added_to_the_navigation_menu_with_config_flat_setting()
    {
        config(['hyde.navigation.subdirectories' => 'flat']);
        $this->directory('_pages/foo');
        Hyde::touch('_pages/foo/bar.md');

        $menu = NavigationMenu::create();
        $expected = collect([
            NavItem::fromRoute(Route::get('index')),
            NavItem::fromRoute(Route::get('foo/bar')),
        ]);

        $this->assertCount(count($expected), $menu->items);
        $this->assertEquals($expected, $menu->items);
    }

    public function test_pages_in_subdirectories_can_be_added_to_the_navigation_menu_with_config_dropdown_setting()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);
        $this->directory('_pages/foo');
        Hyde::touch('_pages/foo/bar.md');

        $menu = NavigationMenu::create();
        $expected = collect([
            NavItem::fromRoute(Route::get('index')),
            NavItem::fromRoute(Route::get('foo/bar')),
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
        $menu = NavigationMenu::create();
        $menu->items->push(NavItem::fromRoute((new MarkdownPage('foo/bar'))->getRoute()));
        $this->assertTrue($menu->hasDropdowns());
    }

    public function test_has_dropdowns_always_returns_false_when_dropdowns_are_disabled()
    {
        $menu = NavigationMenu::create();
        $menu->items->push(NavItem::fromRoute((new MarkdownPage('foo/bar'))->getRoute()));
        $this->assertFalse($menu->hasDropdowns());
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
        $menu = NavigationMenu::create();
        $menu->items->push(NavItem::fromRoute((new MarkdownPage('foo/bar'))->getRoute()));
        $this->assertCount(1, $menu->getDropdowns());

        $this->assertEquals([
            'foo' => [
                NavItem::fromRoute((new MarkdownPage('foo/bar'))->getRoute()),
            ],
        ], $menu->getDropdowns());
    }

    public function test_get_dropdowns_with_multiple_items()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);
        $menu = NavigationMenu::create();

        $menu->items->push(NavItem::fromRoute((new MarkdownPage('foo/bar'))->getRoute()));
        $menu->items->push(NavItem::fromRoute((new MarkdownPage('foo/baz'))->getRoute()));

        $this->assertCount(1, $menu->getDropdowns());

        $this->assertEquals([
            'foo' => [
                NavItem::fromRoute((new MarkdownPage('foo/bar'))->getRoute()),
                NavItem::fromRoute((new MarkdownPage('foo/baz'))->getRoute()),
            ],
        ], $menu->getDropdowns());
    }

    public function test_get_dropdowns_with_multiple_dropdowns()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);
        $menu = NavigationMenu::create();

        $menu->items->push(NavItem::fromRoute((new MarkdownPage('foo/bar'))->getRoute()));
        $menu->items->push(NavItem::fromRoute((new MarkdownPage('foo/baz'))->getRoute()));
        $menu->items->push(NavItem::fromRoute((new MarkdownPage('cat/hat'))->getRoute()));

        $this->assertCount(2, $menu->getDropdowns());

        $this->assertEquals([
            'foo' => [
                NavItem::fromRoute((new MarkdownPage('foo/bar'))->getRoute()),
                NavItem::fromRoute((new MarkdownPage('foo/baz'))->getRoute()),
            ],
            'cat' => [
                NavItem::fromRoute((new MarkdownPage('cat/hat'))->getRoute()),
            ],
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
        $menu = NavigationMenu::create();

        $menu->items->push(NavItem::fromRoute((new DocumentationPage('foo'))->getRoute()));
        $menu->items->push(NavItem::fromRoute((new DocumentationPage('bar/baz'))->getRoute()));

        $this->assertFalse($menu->hasDropdowns());
        $this->assertCount(0, $menu->getDropdowns());
    }

    public function test_blog_posts_do_not_get_added_to_dropdowns()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);
        $menu = NavigationMenu::create();

        $menu->items->push(NavItem::fromRoute((new MarkdownPost('foo'))->getRoute()));
        $menu->items->push(NavItem::fromRoute((new MarkdownPost('bar/baz'))->getRoute()));

        $this->assertFalse($menu->hasDropdowns());
        $this->assertCount(0, $menu->getDropdowns());
    }
}
