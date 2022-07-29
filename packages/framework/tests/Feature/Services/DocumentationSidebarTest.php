<?php

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Framework\Actions\ConvertsArrayToFrontMatter;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\DocumentationSidebar;
use Hyde\Framework\Models\NavItem;
use Hyde\Framework\Models\Route;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Framework\Models\DocumentationSidebar
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
        Hyde::touch(('_docs/index.md'));

        $sidebar = DocumentationSidebar::create();
        $this->assertCount(5, $sidebar->items);
    }

    public function test_files_with_front_matter_hidden_set_to_true_are_removed_from_sidebar()
    {
        $this->createTestFiles();
        File::put(Hyde::path('_docs/test.md'), "---\nhidden: true\n---\n\n# Foo");

        $sidebar = DocumentationSidebar::create();
        $this->assertCount(5, $sidebar->items);
    }

    public function test_sidebar_is_ordered_alphabetically_when_no_order_is_set_in_config()
    {
        Config::set('docs.sidebar_order', []);
        Hyde::touch(('_docs/a.md'));
        Hyde::touch(('_docs/b.md'));
        Hyde::touch(('_docs/c.md'));

        $this->assertEquals(
            collect([
                NavItem::fromRoute(Route::get('docs/a'))->setPriority(500),
                NavItem::fromRoute(Route::get('docs/b'))->setPriority(500),
                NavItem::fromRoute(Route::get('docs/c'))->setPriority(500),
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
        Hyde::touch(('_docs/a.md'));
        Hyde::touch(('_docs/b.md'));
        Hyde::touch(('_docs/c.md'));

        $this->assertEquals(
            collect([
                NavItem::fromRoute(Route::get('docs/c'))->setPriority(250),
                NavItem::fromRoute(Route::get('docs/b'))->setPriority(251),
                NavItem::fromRoute(Route::get('docs/a'))->setPriority(252),
            ]),
            DocumentationSidebar::create()->items
        );
    }

    public function test_sidebar_item_priority_can_be_set_in_front_matter()
    {
        $this->makePage('foo', ['priority' => 25]);

        $this->assertEquals(25, DocumentationSidebar::create()->items->first()->priority);
    }

    public function test_sidebar_item_priority_set_in_config_overrides_front_matter()
    {
        $this->makePage('foo', ['priority' => 25]);

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
        Hyde::touch(('_docs/first.md'));
        Hyde::touch(('_docs/second.md'));
        file_put_contents(Hyde::path('_docs/third.md'),
            (new ConvertsArrayToFrontMatter)->execute(['priority' => 300])
        );

        $this->assertEquals(
            collect([
                NavItem::fromRoute(Route::get('docs/first'))->setPriority(250),
                NavItem::fromRoute(Route::get('docs/second'))->setPriority(252),
                NavItem::fromRoute(Route::get('docs/third'))->setPriority(300),
            ]),
            DocumentationSidebar::create()->items
        );
    }

    public function test_category_can_be_set_in_front_matter()
    {
        $this->makePage('foo', ['category' => 'bar']);

        $this->assertEquals('bar', DocumentationSidebar::create()->items->first()->getGroup());
    }

    public function test_has_groups_returns_false_when_there_are_no_groups()
    {
        $this->assertFalse(DocumentationSidebar::create()->hasGroups());
    }

    public function test_has_groups_returns_true_when_there_are_groups()
    {
        $this->makePage('foo', ['category' => 'bar']);

        $this->assertTrue(DocumentationSidebar::create()->hasGroups());
    }

    public function test_get_groups_returns_empty_array_when_there_are_no_groups()
    {
        $this->assertEquals([], DocumentationSidebar::create()->getGroups());
    }

    public function test_get_groups_returns_array_of_groups_when_there_are_groups()
    {
        $this->makePage('foo', ['category' => 'bar']);

        $this->assertEquals(['bar'], DocumentationSidebar::create()->getGroups());
    }

    public function test_get_groups_returns_array_with_no_duplicates()
    {
        $this->makePage('foo', ['category' => 'bar']);
        $this->makePage('bar', ['category' => 'bar']);
        $this->makePage('baz', ['category' => 'baz']);

        $this->assertEquals(['bar', 'baz'], DocumentationSidebar::create()->getGroups());
    }

    public function test_groups_are_sorted_by_lowest_found_priority_in_each_group()
    {
        $this->makePage('foo', ['category' => 'bar', 'priority' => 100]);
        $this->makePage('bar', ['category' => 'bar', 'priority' => 200]);
        $this->makePage('baz', ['category' => 'baz', 'priority' => 10]);

        $this->assertEquals(['baz', 'bar'], DocumentationSidebar::create()->getGroups());
    }

    public function test_get_items_in_group_returns_empty_collection_when_there_are_no_items()
    {
        $this->assertEquals(collect(), DocumentationSidebar::create()->getItemsInGroup('foo'));
    }

    public function test_get_items_in_group_returns_collection_of_items_in_group()
    {
        $this->makePage('foo', ['category' => 'bar']);
        $this->makePage('bar', ['category' => 'bar']);
        $this->makePage('baz', ['category' => 'baz']);

        $this->assertEquals(
            collect([
                NavItem::fromRoute(Route::get('docs/bar'))->setPriority(500),
                NavItem::fromRoute(Route::get('docs/foo'))->setPriority(500),
            ]),
            DocumentationSidebar::create()->getItemsInGroup('bar')
        );

        $this->assertEquals(
            collect([
                NavItem::fromRoute(Route::get('docs/baz'))->setPriority(500),
            ]),
            DocumentationSidebar::create()->getItemsInGroup('baz')
        );
    }

    public function test_get_items_in_group_normalizes_group_name_to_slug_format()
    {
        $this->makePage('a', ['category' => 'foo bar']);
        $this->makePage('b', ['category' => 'Foo Bar']);
        $this->makePage('c', ['category' => 'foo-bar']);

        $this->assertEquals(
            collect([
                NavItem::fromRoute(Route::get('docs/a'))->setPriority(500),
                NavItem::fromRoute(Route::get('docs/b'))->setPriority(500),
                NavItem::fromRoute(Route::get('docs/c'))->setPriority(500),
            ]),
            DocumentationSidebar::create()->getItemsInGroup('Foo bar')
        );
    }

    public function test_get_items_in_group_does_not_include_items_with_hidden_front_matter()
    {
        $this->makePage('a', ['hidden' => true, 'category' => 'foo']);
        $this->makePage('b', ['category' => 'foo']);

        $this->assertEquals(
            collect([NavItem::fromRoute(Route::get('docs/b'))->setPriority(500)]),
            DocumentationSidebar::create()->getItemsInGroup('foo')
        );
    }

    public function test_get_items_in_group_does_not_include_docs_index()
    {
        Hyde::touch('_docs/foo.md');
        Hyde::touch('_docs/index.md');

        $this->assertEquals(
            collect([NavItem::fromRoute(Route::get('docs/foo'))->setPriority(500)]),
            DocumentationSidebar::create()->items
        );
    }

    protected function createTestFiles(int $count = 5): void
    {
        for ($i = 0; $i < $count; $i++) {
            Hyde::touch('_docs/test-'.$i.'.md');
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
