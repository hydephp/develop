<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Facades\Filesystem;
use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Actions\ConvertsArrayToFrontMatter;
use Hyde\Framework\Features\Navigation\DocumentationSidebar;
use Hyde\Framework\Features\Navigation\NavItem;
use Hyde\Hyde;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Facades\Render;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Framework\Features\Navigation\DocumentationSidebar
 * @covers \Hyde\Framework\Factories\Concerns\HasFactory
 * @covers \Hyde\Framework\Factories\NavigationDataFactory
 * @covers \Hyde\Framework\Features\Navigation\NavItem
 */
class DocumentationSidebarTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->resetDocs();
    }

    protected function tearDown(): void
    {
        $this->resetDocs();

        parent::tearDown();
    }

    public function test_sidebar_can_be_created()
    {
        $sidebar = DocumentationSidebar::create();

        $this->assertInstanceOf(DocumentationSidebar::class, $sidebar);
    }

    public function test_sidebar_items_are_added_automatically()
    {
        $this->createTestFiles();

        $sidebar = DocumentationSidebar::create();

        $this->assertCount(5, $sidebar->items);
    }

    public function test_index_page_is_removed_from_sidebar()
    {
        $this->createTestFiles();
        Filesystem::touch('_docs/index.md');

        $sidebar = DocumentationSidebar::create();
        $this->assertCount(5, $sidebar->items);
    }

    public function test_files_with_front_matter_hidden_set_to_true_are_removed_from_sidebar()
    {
        $this->createTestFiles();
        File::put(Hyde::path('_docs/test.md'), "---\nnavigation:\n    hidden: true\n---\n\n# Foo");

        $sidebar = DocumentationSidebar::create();
        $this->assertCount(5, $sidebar->items);
    }

    public function test_sidebar_is_ordered_alphabetically_when_no_order_is_set_in_config()
    {
        Config::set('docs.sidebar_order', []);
        Filesystem::touch('_docs/a.md');
        Filesystem::touch('_docs/b.md');
        Filesystem::touch('_docs/c.md');

        $this->assertEquals(
            collect([
                NavItem::fromRoute(Routes::get('docs/a'), priority: 999),
                NavItem::fromRoute(Routes::get('docs/b'), priority: 999),
                NavItem::fromRoute(Routes::get('docs/c'), priority: 999),
            ]),
            DocumentationSidebar::create()->items
        );
    }

    public function test_sidebar_is_ordered_by_priority_when_priority_is_set_in_config()
    {
        Config::set('docs.sidebar_order', [
            'c',
            'b',
            'a',
        ]);
        Filesystem::touch('_docs/a.md');
        Filesystem::touch('_docs/b.md');
        Filesystem::touch('_docs/c.md');

        $this->assertEquals(
            collect([
                NavItem::fromRoute(Routes::get('docs/c'), priority: 250 + 250),
                NavItem::fromRoute(Routes::get('docs/b'), priority: 250 + 251),
                NavItem::fromRoute(Routes::get('docs/a'), priority: 250 + 252),
            ]),
            DocumentationSidebar::create()->items
        );
    }

    public function test_sidebar_item_priority_can_be_set_in_front_matter()
    {
        $this->makePage('foo', ['navigation.priority' => 25]);

        $this->assertEquals(25, DocumentationSidebar::create()->items->first()->priority);
    }

    public function test_sidebar_item_priority_set_in_config_overrides_front_matter()
    {
        $this->makePage('foo', ['navigation.priority' => 25]);

        Config::set('docs.sidebar_order', ['foo']);

        $this->assertEquals(25, DocumentationSidebar::create()->items->first()->priority);
    }

    public function test_sidebar_priorities_can_be_set_in_both_front_matter_and_config()
    {
        Config::set('docs.sidebar_order', [
            'first',
            'third',
            'second',
        ]);
        Filesystem::touch('_docs/first.md');
        Filesystem::touch('_docs/second.md');
        file_put_contents(Hyde::path('_docs/third.md'),
            (new ConvertsArrayToFrontMatter)->execute(['navigation.priority' => 250 + 300])
        );

        $this->assertEquals(
            collect([
                NavItem::fromRoute(Routes::get('docs/first'), priority: 250 + 250),
                NavItem::fromRoute(Routes::get('docs/second'), priority: 250 + 252),
                NavItem::fromRoute(Routes::get('docs/third'), priority: 250 + 300),
            ]),
            DocumentationSidebar::create()->items
        );
    }

    public function test_group_can_be_set_in_front_matter()
    {
        $this->makePage('foo', ['navigation.group' => 'bar']);

        $this->assertEquals('bar', DocumentationSidebar::create()->items->first()->getGroup());
    }

    public function test_has_groups_returns_false_when_there_are_no_groups()
    {
        $this->assertFalse(DocumentationSidebar::create()->hasGroups());
    }

    public function test_has_groups_returns_true_when_there_are_groups()
    {
        $this->makePage('foo', ['navigation.group' => 'bar']);

        $this->assertTrue(DocumentationSidebar::create()->hasGroups());
    }

    public function test_has_groups_returns_true_when_there_are_multiple_groups()
    {
        $this->makePage('foo', ['navigation.group' => 'bar']);
        $this->makePage('bar', ['navigation.group' => 'baz']);

        $this->assertTrue(DocumentationSidebar::create()->hasGroups());
    }

    public function test_has_groups_returns_true_when_there_are_multiple_groups_mixed_with_defaults()
    {
        $this->makePage('foo', ['navigation.group' => 'bar']);
        $this->makePage('bar', ['navigation.group' => 'baz']);
        $this->makePage('baz');

        $this->assertTrue(DocumentationSidebar::create()->hasGroups());
    }

    public function test_get_groups_returns_empty_array_when_there_are_no_groups()
    {
        $this->assertEquals([], DocumentationSidebar::create()->getGroups());
    }

    public function test_get_groups_returns_array_of_groups_when_there_are_groups()
    {
        $this->makePage('foo', ['navigation.group' => 'bar']);

        $this->assertEquals(['bar'], DocumentationSidebar::create()->getGroups());
    }

    public function test_get_groups_returns_array_with_no_duplicates()
    {
        $this->makePage('foo', ['navigation.group' => 'bar']);
        $this->makePage('bar', ['navigation.group' => 'bar']);
        $this->makePage('baz', ['navigation.group' => 'baz']);

        $this->assertEquals(['bar', 'baz'], DocumentationSidebar::create()->getGroups());
    }

    public function test_groups_are_sorted_by_lowest_found_priority_in_each_group()
    {
        $this->makePage('foo', ['navigation.group' => 'bar', 'navigation.priority' => 100]);
        $this->makePage('bar', ['navigation.group' => 'bar', 'navigation.priority' => 200]);
        $this->makePage('baz', ['navigation.group' => 'baz', 'navigation.priority' => 10]);

        $this->assertEquals(['baz', 'bar'], DocumentationSidebar::create()->getGroups());
    }

    public function test_get_items_in_group_returns_empty_collection_when_there_are_no_items()
    {
        $this->assertEquals(collect(), DocumentationSidebar::create()->getItemsInGroup('foo'));
    }

    public function test_get_items_in_group_returns_collection_of_items_in_group()
    {
        $this->makePage('foo', ['navigation.group' => 'bar']);
        $this->makePage('bar', ['navigation.group' => 'bar']);
        $this->makePage('baz', ['navigation.group' => 'baz']);

        $this->assertEquals(
            collect([
                NavItem::fromRoute(Routes::get('docs/bar'), priority: 999),
                NavItem::fromRoute(Routes::get('docs/foo'), priority: 999),
            ]),
            DocumentationSidebar::create()->getItemsInGroup('bar')
        );

        $this->assertEquals(
            collect([
                NavItem::fromRoute(Routes::get('docs/baz'), priority: 999),
            ]),
            DocumentationSidebar::create()->getItemsInGroup('baz')
        );
    }

    public function test_get_items_in_group_normalizes_group_name_to_slug_format()
    {
        $this->makePage('a', ['navigation.group' => 'foo bar']);
        $this->makePage('b', ['navigation.group' => 'Foo Bar']);
        $this->makePage('c', ['navigation.group' => 'foo-bar']);

        $this->assertEquals(
            collect([
                NavItem::fromRoute(Routes::get('docs/a'), priority: 999),
                NavItem::fromRoute(Routes::get('docs/b'), priority: 999),
                NavItem::fromRoute(Routes::get('docs/c'), priority: 999),
            ]),
            DocumentationSidebar::create()->getItemsInGroup('Foo bar')
        );
    }

    public function test_get_items_in_group_does_not_include_items_with_hidden_front_matter()
    {
        $this->makePage('a', ['navigation.hidden' => true, 'navigation.group' => 'foo']);
        $this->makePage('b', ['navigation.group' => 'foo']);

        $this->assertEquals(
            collect([NavItem::fromRoute(Routes::get('docs/b'), priority: 999)]),
            DocumentationSidebar::create()->getItemsInGroup('foo')
        );
    }

    public function test_get_items_in_group_does_not_include_docs_index()
    {
        Filesystem::touch('_docs/foo.md');
        Filesystem::touch('_docs/index.md');

        $this->assertEquals(
            collect([NavItem::fromRoute(Routes::get('docs/foo'), priority: 999)]),
            DocumentationSidebar::create()->items
        );
    }

    public function test_is_group_active_returns_false_when_supplied_group_is_not_active()
    {
        Render::setPage(new DocumentationPage(matter: ['navigation.group' => 'foo']));
        $this->assertFalse(DocumentationSidebar::create()->isGroupActive('bar'));
    }

    public function test_is_group_active_returns_true_when_supplied_group_is_active()
    {
        Render::setPage(new DocumentationPage(matter: ['navigation.group' => 'foo']));
        $this->assertTrue(DocumentationSidebar::create()->isGroupActive('foo'));
    }

    public function test_is_group_active_returns_true_for_differing_casing()
    {
        Render::setPage(new DocumentationPage(matter: ['navigation.group' => 'Foo Bar']));
        $this->assertTrue(DocumentationSidebar::create()->isGroupActive('foo-bar'));
    }

    public function test_is_group_active_returns_true_first_group_of_index_page()
    {
        $this->makePage('index');
        $this->makePage('foo', ['navigation.group' => 'foo']);
        $this->makePage('bar', ['navigation.group' => 'bar']);
        $this->makePage('baz', ['navigation.group' => 'baz']);

        Render::setPage(DocumentationPage::get('index'));
        $this->assertTrue(DocumentationSidebar::create()->isGroupActive('bar'));
        $this->assertFalse(DocumentationSidebar::create()->isGroupActive('foo'));
        $this->assertFalse(DocumentationSidebar::create()->isGroupActive('baz'));
    }

    public function test_is_group_active_returns_true_first_sorted_group_of_index_page()
    {
        $this->makePage('index');
        $this->makePage('foo', ['navigation.group' => 'foo', 'navigation.priority' => 1]);
        $this->makePage('bar', ['navigation.group' => 'bar', 'navigation.priority' => 2]);
        $this->makePage('baz', ['navigation.group' => 'baz', 'navigation.priority' => 3]);

        Render::setPage(DocumentationPage::get('index'));
        $this->assertTrue(DocumentationSidebar::create()->isGroupActive('foo'));
        $this->assertFalse(DocumentationSidebar::create()->isGroupActive('bar'));
        $this->assertFalse(DocumentationSidebar::create()->isGroupActive('baz'));
    }

    public function test_automatic_index_page_group_expansion_respects_custom_navigation_menu_settings()
    {
        $this->makePage('index', ['navigation.group' => 'baz']);
        $this->makePage('foo', ['navigation.group' => 'foo', 'navigation.priority' => 1]);
        $this->makePage('bar', ['navigation.group' => 'bar', 'navigation.priority' => 2]);
        $this->makePage('baz', ['navigation.group' => 'baz', 'navigation.priority' => 3]);

        Render::setPage(DocumentationPage::get('index'));
        $this->assertFalse(DocumentationSidebar::create()->isGroupActive('foo'));
        $this->assertFalse(DocumentationSidebar::create()->isGroupActive('bar'));
        $this->assertTrue(DocumentationSidebar::create()->isGroupActive('baz'));
    }

    public function test_can_have_multiple_grouped_pages_with_the_same_name_labels()
    {
        $this->makePage('foo', ['navigation.group' => 'foo', 'navigation.label' => 'Foo']);
        $this->makePage('bar', ['navigation.group' => 'bar', 'navigation.label' => 'Foo']);

        $sidebar = DocumentationSidebar::create();
        $this->assertCount(2, $sidebar->items);

        $this->assertEquals(
            collect([
                NavItem::fromRoute(Routes::get('docs/bar'), priority: 999),
                NavItem::fromRoute(Routes::get('docs/foo'), priority: 999),
            ]),
            $sidebar->items
        );
    }

    public function test_duplicate_labels_within_the_same_group_is_removed()
    {
        $this->makePage('foo', ['navigation.group' => 'foo', 'navigation.label' => 'Foo']);
        $this->makePage('bar', ['navigation.group' => 'foo', 'navigation.label' => 'Foo']);

        $sidebar = DocumentationSidebar::create();
        $this->assertCount(1, $sidebar->items);

        $this->assertEquals(
            collect([NavItem::fromRoute(Routes::get('docs/bar'), priority: 999)]),
            $sidebar->items
        );
    }

    public function test_is_group_active_for_index_page_with_no_groups()
    {
        $this->makePage('index');

        Render::setPage(DocumentationPage::get('index'));
        $this->assertFalse(DocumentationSidebar::create()->isGroupActive('foo'));
    }

    public function test_index_page_added_to_sidebar_when_it_is_the_only_page()
    {
        Filesystem::touch('_docs/index.md');
        $sidebar = DocumentationSidebar::create();

        $this->assertCount(1, $sidebar->items);
        $this->assertEquals(
            collect([NavItem::fromRoute(Routes::get('docs/index'))]),
            $sidebar->items
        );
    }

    public function test_index_page_not_added_to_sidebar_when_other_pages_exist()
    {
        $this->createTestFiles(1);
        Filesystem::touch('_docs/index.md');
        $sidebar = DocumentationSidebar::create();

        $this->assertCount(1, $sidebar->items);
        $this->assertEquals(
            collect([NavItem::fromRoute(Routes::get('docs/test-0'))]),
            $sidebar->items
        );
    }

    protected function createTestFiles(int $count = 5): void
    {
        for ($i = 0; $i < $count; $i++) {
            Filesystem::touch('_docs/test-'.$i.'.md');
        }
    }

    protected function makePage(string $name, ?array $matter = null): void
    {
        file_put_contents(
            Hyde::path('_docs/'.$name.'.md'),
            (new ConvertsArrayToFrontMatter)->execute($matter ?? [])
        );
    }
}
