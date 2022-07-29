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
        Hyde::touch(('_docs/alpha.md'));
        Hyde::touch(('_docs/bravo.md'));
        Hyde::touch(('_docs/charlie.md'));

        $this->assertEquals(
            collect([
                NavItem::fromRoute(Route::get('docs/alpha'))->setPriority(500),
                NavItem::fromRoute(Route::get('docs/bravo'))->setPriority(500),
                NavItem::fromRoute(Route::get('docs/charlie'))->setPriority(500),
            ]),
            DocumentationSidebar::create()->items
        );
    }

    public function test_sidebar_is_ordered_by_priority_when_priority_is_set_in_config()
    {
        Config::set('docs.sidebar_order', [
            'charlie',
            'bravo',
            'alpha',
        ]);
        Hyde::touch(('_docs/alpha.md'));
        Hyde::touch(('_docs/bravo.md'));
        Hyde::touch(('_docs/charlie.md'));

        $this->assertEquals(
            collect([
                NavItem::fromRoute(Route::get('docs/charlie'))->setPriority(250),
                NavItem::fromRoute(Route::get('docs/bravo'))->setPriority(251),
                NavItem::fromRoute(Route::get('docs/alpha'))->setPriority(252),
            ]),
            DocumentationSidebar::create()->items
        );
    }

    public function test_sidebar_item_priority_can_be_set_in_front_matter()
    {
        file_put_contents(
            Hyde::path('_docs/foo.md'),
            (new ConvertsArrayToFrontMatter)->execute([
                'priority' => 25,
            ])
        );

        $this->assertEquals(25, DocumentationSidebar::create()->items->first()->priority);
    }

    public function test_sidebar_item_priority_set_in_config_overrides_front_matter()
    {
        file_put_contents(Hyde::path('_docs/foo.md'),
            (new ConvertsArrayToFrontMatter)->execute(['priority' => 25])
        );

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
        file_put_contents(
            Hyde::path('_docs/foo.md'),
                (new ConvertsArrayToFrontMatter)->execute([
                    'category' => 'bar',
                ])
        );

        $this->assertEquals('bar', DocumentationSidebar::create()->items->first()->getGroup());
    }

    public function test_has_groups_returns_false_when_there_are_no_groups()
    {
        $this->assertFalse(DocumentationSidebar::create()->hasGroups());
    }

    public function test_has_groups_returns_true_when_there_are_groups()
    {
        file_put_contents(
            Hyde::path('_docs/foo.md'),
                (new ConvertsArrayToFrontMatter)->execute([
                    'category' => 'bar',
                ])
        );

        $this->assertTrue(DocumentationSidebar::create()->hasGroups());
    }

    public function test_get_groups_returns_empty_array_when_there_are_no_groups()
    {
        $this->assertEquals([], DocumentationSidebar::create()->getGroups());
    }

    public function test_get_groups_returns_array_of_groups_when_there_are_groups()
    {
        file_put_contents(
            Hyde::path('_docs/foo.md'),
                (new ConvertsArrayToFrontMatter)->execute([
                    'category' => 'bar',
                ])
        );

        $this->assertEquals(['bar'], DocumentationSidebar::create()->getGroups());
    }

    public function test_get_groups_returns_array_with_no_duplicates()
    {
        file_put_contents(
            Hyde::path('_docs/foo.md'),
                (new ConvertsArrayToFrontMatter)->execute([
                    'category' => 'bar',
                ])
        );
        file_put_contents(
            Hyde::path('_docs/bar.md'),
                (new ConvertsArrayToFrontMatter)->execute([
                    'category' => 'bar',
                ])
        );
        file_put_contents(
            Hyde::path('_docs/baz.md'),
                (new ConvertsArrayToFrontMatter)->execute([
                    'category' => 'baz',
                ])
        );

        $this->assertEquals(['bar', 'baz'], DocumentationSidebar::create()->getGroups());
    }

    public function test_groups_are_sorted_by_lowest_found_priority_in_each_group()
    {
        file_put_contents(
            Hyde::path('_docs/foo.md'),
                (new ConvertsArrayToFrontMatter)->execute([
                    'category' => 'bar',
                    'priority' => 100,
                ])
        );
        file_put_contents(
            Hyde::path('_docs/bar.md'),
                (new ConvertsArrayToFrontMatter)->execute([
                    'category' => 'bar',
                    'priority' => 200,
                ])
        );
        file_put_contents(
            Hyde::path('_docs/baz.md'),
                (new ConvertsArrayToFrontMatter)->execute([
                    'category' => 'baz',
                    'priority' => 10,
                ])
        );

        $this->assertEquals(['baz', 'bar'], DocumentationSidebar::create()->getGroups());
    }

    public function test_get_items_in_group_returns_empty_collection_when_there_are_no_items()
    {
        $this->assertEquals(collect(), DocumentationSidebar::create()->getItemsInGroup('foo'));
    }

    public function test_get_items_in_group_returns_collection_of_items_in_group()
    {
        file_put_contents(
            Hyde::path('_docs/foo.md'),
                (new ConvertsArrayToFrontMatter)->execute([
                    'category' => 'bar',
                ])
        );
        file_put_contents(
            Hyde::path('_docs/bar.md'),
                (new ConvertsArrayToFrontMatter)->execute([
                    'category' => 'bar',
                ])
        );
        file_put_contents(
            Hyde::path('_docs/baz.md'),
                (new ConvertsArrayToFrontMatter)->execute([
                    'category' => 'baz',
                ])
        );

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
        file_put_contents(
            Hyde::path('_docs/a.md'),
                (new ConvertsArrayToFrontMatter)->execute([
                    'category' => 'foo bar',
                ])
        );
        file_put_contents(
            Hyde::path('_docs/b.md'),
                (new ConvertsArrayToFrontMatter)->execute([
                    'category' => 'Foo Bar',
                ])
        );
        file_put_contents(
            Hyde::path('_docs/c.md'),
                (new ConvertsArrayToFrontMatter)->execute([
                    'category' => 'foo-bar',
                ])
        );

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
        file_put_contents(
            Hyde::path('_docs/a.md'),
                (new ConvertsArrayToFrontMatter)->execute([
                    'category' => 'foo',
                    'hidden' => true,
                ])
        );
        file_put_contents(
            Hyde::path('_docs/b.md'),
                (new ConvertsArrayToFrontMatter)->execute([
                    'category' => 'foo',
                ])
        );

        $this->assertEquals(
            collect([
                NavItem::fromRoute(Route::get('docs/b'))->setPriority(500),
            ]),
            DocumentationSidebar::create()->getItemsInGroup('foo')
        );
    }

    public function test_get_items_in_group_does_not_include_docs_index()
    {
        Hyde::touch('_docs/foo.md');
        Hyde::touch('_docs/index.md');

        $this->assertEquals(
            collect([
                NavItem::fromRoute(Route::get('docs/foo'))->setPriority(500),
            ]),
            DocumentationSidebar::create()->items
        );
    }

    protected function createTestFiles(int $count = 5): void
    {
        for ($i = 0; $i < $count; $i++) {
            Hyde::touch('_docs/test-'.$i.'.md');
        }
    }
}
