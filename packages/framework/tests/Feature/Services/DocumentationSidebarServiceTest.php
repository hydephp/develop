<?php

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Framework\Actions\ConvertsArrayToFrontMatter;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\DocumentationSidebar;
use Hyde\Framework\Models\DocumentationSidebarItem;
use Hyde\Framework\Services\DocumentationSidebarService;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Framework\Services\DocumentationSidebarService
 * @covers \Hyde\Framework\Models\DocumentationSidebarItem
 * @covers \Hyde\Framework\Models\DocumentationSidebar
 */
class DocumentationSidebarServiceTest extends TestCase
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
        $service = new DocumentationSidebarService();
        $sidebar = $service->createSidebar()->getSidebar();

        $this->assertInstanceOf(DocumentationSidebar::class, $sidebar);
    }

    public function test_sidebar_items_can_be_added()
    {
        $sidebar = DocumentationSidebarService::get()->addItem(
            new DocumentationSidebarItem('test', 'test')
        );

        $this->assertCount(1, $sidebar);
        $this->assertInstanceOf(DocumentationSidebarItem::class, $sidebar[0]);
    }

    public function test_sidebar_items_are_added_automatically()
    {
        $this->createTestFiles();

        $sidebar = DocumentationSidebarService::get();

        $this->assertCount(5, $sidebar);
    }

    public function test_index_page_is_removed_from_sidebar()
    {
        $this->createTestFiles();
        Hyde::touch(('_docs/index.md'));

        $sidebar = DocumentationSidebarService::get();
        $this->assertCount(5, $sidebar);
    }

    public function test_files_with_front_matter_hidden_set_to_true_are_removed_from_sidebar()
    {
        $this->createTestFiles();
        File::put(Hyde::path('_docs/test.md'), "---\nhidden: true\n---\n\n# Foo");

        $sidebar = DocumentationSidebarService::get();
        $this->assertCount(5, $sidebar);
    }

    public function test_sidebar_is_ordered_alphabetically_when_no_order_is_set_in_config()
    {
        Config::set('docs.sidebar_order', []);
        Hyde::touch(('_docs/alpha.md'));
        Hyde::touch(('_docs/bravo.md'));
        Hyde::touch(('_docs/charlie.md'));

        $sidebar = DocumentationSidebarService::get();

        $this->assertEquals('alpha', $sidebar[0]->destination);
        $this->assertEquals('bravo', $sidebar[1]->destination);
        $this->assertEquals('charlie', $sidebar[2]->destination);
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

        $sidebar = DocumentationSidebarService::get();
        $this->assertEquals('charlie', $sidebar[0]->destination);
        $this->assertEquals('bravo', $sidebar[1]->destination);
        $this->assertEquals('alpha', $sidebar[2]->destination);
    }

    public function test_sidebar_item_priority_can_be_set_in_front_matter()
    {
        file_put_contents(
            Hyde::path('_docs/foo.md'),
            (new ConvertsArrayToFrontMatter)->execute([
                'priority' => 25,
            ])
        );

        $this->assertEquals(25, DocumentationSidebarItem::parseFromFile('foo')->priority);
        $this->assertEquals(25, DocumentationSidebarService::get()->first()->priority);
    }

    public function test_sidebar_item_priority_set_in_config_overrides_front_matter()
    {
        file_put_contents(Hyde::path('_docs/foo.md'),
            (new ConvertsArrayToFrontMatter)->execute(['priority' => 25])
        );

        Config::set('docs.sidebar_order', ['foo']);

        $this->assertEquals(25, DocumentationSidebarService::get()->first()->priority);
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
        $sidebar = DocumentationSidebarService::get();
        $this->assertEquals('first', $sidebar[0]->destination);
        $this->assertEquals('second', $sidebar[1]->destination);
        $this->assertEquals('third', $sidebar[2]->destination);
    }

    public function test_category_can_be_set_in_front_matter()
    {
        file_put_contents(
            Hyde::path('_docs/foo.md'),
                (new ConvertsArrayToFrontMatter)->execute([
                    'category' => 'bar',
                ])
        );

        $this->assertEquals('bar', DocumentationSidebarItem::parseFromFile('foo')->category);
        $this->assertEquals('bar', DocumentationSidebarService::get()->first()->category);
    }

    public function test_sidebar_categories_are_assembled_from_sidebar_items()
    {
        $service = (new DocumentationSidebarService)->createSidebar();
        $service->addItem(new DocumentationSidebarItem('foo', 'foo', category: 'foo'));
        $service->addItem(new DocumentationSidebarItem('bar', 'bar', category: 'foo'));
        $service->addItem(new DocumentationSidebarItem('cat', 'cat', category: 'cat'));
        $service->addItem(new DocumentationSidebarItem('hat', 'hat'));

        $this->assertEquals(['foo', 'cat', 'other'], $service->getCategories());
    }

    public function test_has_categories_returns_false_if_no_categories_are_set()
    {
        $service = (new DocumentationSidebarService)->createSidebar();
        $service->addItem(new DocumentationSidebarItem('foo', 'foo'));

        $this->assertFalse($service->hasCategories());
    }

    public function test_has_categories_returns_true_if_at_least_one_category_is_set()
    {
        $service = (new DocumentationSidebarService)->createSidebar();
        $service->addItem(new DocumentationSidebarItem('foo', 'foo', category: 'foo'));
        $service->addItem(new DocumentationSidebarItem('bar', 'bar'));

        $this->assertTrue($service->hasCategories());
    }

    public function test_get_items_in_category_returns_items_with_given_category()
    {
        $service = (new DocumentationSidebarService)->createSidebar();

        $service->addItem($foo = new DocumentationSidebarItem('foo', 'foo', category: 'foo'));
        $service->addItem(new DocumentationSidebarItem('bar', 'bar', category: 'foo'));
        $service->addItem(new DocumentationSidebarItem('cat', 'cat', category: 'cat'));
        $service->addItem(new DocumentationSidebarItem('hat', 'hat'));

        $this->assertCount(2, $service->getItemsInCategory('foo'));
        $this->assertCount(1, $service->getItemsInCategory('cat'));
        $this->assertCount(0, $service->getItemsInCategory('hat'));

        $this->assertEquals($foo, $service->getItemsInCategory('foo')->first());
    }

    public function test_items_with_no_category_gets_added_to_the_default_category_when_at_least_one_category_is_set()
    {
        $service = DocumentationSidebarService::create();

        $service->addItem(new DocumentationSidebarItem('foo', 'foo', category: 'foo'));
        $service->addItem(new DocumentationSidebarItem('bar', 'bar'));

        $categories = $service->getCategories();

        $this->assertCount(2, $categories);
        $this->assertCount(1, $service->getItemsInCategory('foo'));
        $this->assertCount(1, $service->getItemsInCategory('other'));
        $this->assertEquals('foo', $service->getItemsInCategory('foo')->first()->category);
        $this->assertEquals('other', $service->getItemsInCategory('other')->first()->category);
    }

    public function test_items_with_no_category_gets_added_to_the_default_category_when_no_categories_are_set()
    {
        $service = DocumentationSidebarService::create();
        $service->addItem(new DocumentationSidebarItem('foo', 'foo'));
        $service->addItem(new DocumentationSidebarItem('bar', 'bar'));

        $categories = $service->getCategories();

        $this->assertCount(0, $categories);
    }

    public function test_category_names_are_case_insensitive()
    {
        $service = DocumentationSidebarService::create();
        $service->addItem(new DocumentationSidebarItem('foo', 'foo', category: 'Foo Bar'));
        $service->addItem(new DocumentationSidebarItem('bar', 'bar', category: 'foo bar'));
        $service->addItem(new DocumentationSidebarItem('cat', 'cat', category: 'Foo_bar'));
        $categories = $service->getCategories();

        $this->assertCount(1, $categories);
        $this->assertCount(3, $service->getItemsInCategory('foo bar'));
    }

    public function test_get_sorted_categories_returns_categories_sorted_by_priority()
    {
        $service = DocumentationSidebarService::create();
        $service->addItem(new DocumentationSidebarItem('third', 'foo', priority: 3, category: 'third'));
        $service->addItem(new DocumentationSidebarItem('second', 'foo', priority: 2, category: 'second'));
        $service->addItem(new DocumentationSidebarItem('first', 'foo', priority: 1, category: 'first'));

        $categories = $service->getCategories();

        $this->assertCount(3, $categories);
        $this->assertEquals('first', $categories[0]);
        $this->assertEquals('second', $categories[1]);
        $this->assertEquals('third', $categories[2]);
    }

    protected function createTestFiles(int $count = 5): void
    {
        for ($i = 0; $i < $count; $i++) {
            Hyde::touch(('_docs/test-'.$i.'.md'));
        }
    }
}
