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

    public function testSidebarCanBeCreated()
    {
        $sidebar = DocumentationSidebar::create();

        $this->assertInstanceOf(DocumentationSidebar::class, $sidebar);
    }

    public function testSidebarItemsAreAddedAutomatically()
    {
        $this->createTestFiles();

        $sidebar = DocumentationSidebar::create();

        $this->assertCount(5, $sidebar->items);
    }

    public function testIndexPageIsRemovedFromSidebar()
    {
        $this->createTestFiles();
        Filesystem::touch('_docs/index.md');

        $sidebar = DocumentationSidebar::create();
        $this->assertCount(5, $sidebar->items);
    }

    public function testFilesWithFrontMatterHiddenSetToTrueAreRemovedFromSidebar()
    {
        $this->createTestFiles();
        File::put(Hyde::path('_docs/test.md'), "---\nnavigation:\n    hidden: true\n---\n\n# Foo");

        $sidebar = DocumentationSidebar::create();
        $this->assertCount(5, $sidebar->items);
    }

    public function testSidebarIsOrderedAlphabeticallyWhenNoOrderIsSetInConfig()
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

    public function testSidebarIsOrderedByPriorityWhenPriorityIsSetInConfig()
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

    public function testSidebarItemPriorityCanBeSetInFrontMatter()
    {
        $this->makePage('foo', ['navigation.priority' => 25]);

        $this->assertEquals(25, DocumentationSidebar::create()->items->first()->priority);
    }

    public function testSidebarItemPrioritySetInConfigOverridesFrontMatter()
    {
        $this->makePage('foo', ['navigation.priority' => 25]);

        Config::set('docs.sidebar_order', ['foo']);

        $this->assertEquals(25, DocumentationSidebar::create()->items->first()->priority);
    }

    public function testSidebarPrioritiesCanBeSetInBothFrontMatterAndConfig()
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

    public function testGroupCanBeSetInFrontMatter()
    {
        $this->makePage('foo', ['navigation.group' => 'bar']);

        $this->assertEquals('bar', DocumentationSidebar::create()->items->first()->getGroup());
    }

    public function testHasGroupsReturnsFalseWhenThereAreNoGroups()
    {
        $this->assertFalse(DocumentationSidebar::create()->hasGroups());
    }

    public function testHasGroupsReturnsTrueWhenThereAreGroups()
    {
        $this->makePage('foo', ['navigation.group' => 'bar']);

        $this->assertTrue(DocumentationSidebar::create()->hasGroups());
    }

    public function testHasGroupsReturnsTrueWhenThereAreMultipleGroups()
    {
        $this->makePage('foo', ['navigation.group' => 'bar']);
        $this->makePage('bar', ['navigation.group' => 'baz']);

        $this->assertTrue(DocumentationSidebar::create()->hasGroups());
    }

    public function testHasGroupsReturnsTrueWhenThereAreMultipleGroupsMixedWithDefaults()
    {
        $this->makePage('foo', ['navigation.group' => 'bar']);
        $this->makePage('bar', ['navigation.group' => 'baz']);
        $this->makePage('baz');

        $this->assertTrue(DocumentationSidebar::create()->hasGroups());
    }

    public function testGetGroupsReturnsEmptyArrayWhenThereAreNoGroups()
    {
        $this->assertEquals([], DocumentationSidebar::create()->getGroups());
    }

    public function testGetGroupsReturnsArrayOfGroupsWhenThereAreGroups()
    {
        $this->makePage('foo', ['navigation.group' => 'bar']);

        $this->assertEquals(['bar'], DocumentationSidebar::create()->getGroups());
    }

    public function testGetGroupsReturnsArrayWithNoDuplicates()
    {
        $this->makePage('foo', ['navigation.group' => 'bar']);
        $this->makePage('bar', ['navigation.group' => 'bar']);
        $this->makePage('baz', ['navigation.group' => 'baz']);

        $this->assertEquals(['bar', 'baz'], DocumentationSidebar::create()->getGroups());
    }

    public function testGroupsAreSortedByLowestFoundPriorityInEachGroup()
    {
        $this->makePage('foo', ['navigation.group' => 'bar', 'navigation.priority' => 100]);
        $this->makePage('bar', ['navigation.group' => 'bar', 'navigation.priority' => 200]);
        $this->makePage('baz', ['navigation.group' => 'baz', 'navigation.priority' => 10]);

        $this->assertEquals(['baz', 'bar'], DocumentationSidebar::create()->getGroups());
    }

    public function testGetItemsInGroupReturnsEmptyCollectionWhenThereAreNoItems()
    {
        $this->assertEquals(collect(), DocumentationSidebar::create()->getItemsInGroup('foo'));
    }

    public function testGetItemsInGroupReturnsCollectionOfItemsInGroup()
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

    public function testGetItemsInGroupNormalizesGroupNameToSlugFormat()
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

    public function testGetItemsInGroupDoesNotIncludeItemsWithHiddenFrontMatter()
    {
        $this->makePage('a', ['navigation.hidden' => true, 'navigation.group' => 'foo']);
        $this->makePage('b', ['navigation.group' => 'foo']);

        $this->assertEquals(
            collect([NavItem::fromRoute(Routes::get('docs/b'), priority: 999)]),
            DocumentationSidebar::create()->getItemsInGroup('foo')
        );
    }

    public function testGetItemsInGroupDoesNotIncludeDocsIndex()
    {
        Filesystem::touch('_docs/foo.md');
        Filesystem::touch('_docs/index.md');

        $this->assertEquals(
            collect([NavItem::fromRoute(Routes::get('docs/foo'), priority: 999)]),
            DocumentationSidebar::create()->items
        );
    }

    public function testIsGroupActiveReturnsFalseWhenSuppliedGroupIsNotActive()
    {
        Render::setPage(new DocumentationPage(matter: ['navigation.group' => 'foo']));
        $this->assertFalse(DocumentationSidebar::create()->isGroupActive('bar'));
    }

    public function testIsGroupActiveReturnsTrueWhenSuppliedGroupIsActive()
    {
        Render::setPage(new DocumentationPage(matter: ['navigation.group' => 'foo']));
        $this->assertTrue(DocumentationSidebar::create()->isGroupActive('foo'));
    }

    public function testIsGroupActiveReturnsTrueForDifferingCasing()
    {
        Render::setPage(new DocumentationPage(matter: ['navigation.group' => 'Foo Bar']));
        $this->assertTrue(DocumentationSidebar::create()->isGroupActive('foo-bar'));
    }

    public function testIsGroupActiveReturnsTrueFirstGroupOfIndexPage()
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

    public function testIsGroupActiveReturnsTrueFirstSortedGroupOfIndexPage()
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

    public function testAutomaticIndexPageGroupExpansionRespectsCustomNavigationMenuSettings()
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

    public function testMakeGroupTitleTurnsGroupKeyIntoTitle()
    {
        $this->assertSame('Hello World', DocumentationSidebar::create()->makeGroupTitle('hello world'));
        $this->assertSame('Hello World', DocumentationSidebar::create()->makeGroupTitle('hello-world'));
        $this->assertSame('Hello World', DocumentationSidebar::create()->makeGroupTitle('hello_world'));
        $this->assertSame('Hello World', DocumentationSidebar::create()->makeGroupTitle('helloWorld'));
    }

    public function testMakeGroupTitleUsesConfiguredSidebarGroupLabelsWhenAvailable()
    {
        Config::set('docs.sidebar_group_labels', [
            'example' => 'Hello world!',
        ]);

        $this->assertSame('Hello world!', DocumentationSidebar::create()->makeGroupTitle('example'));
        $this->assertSame('Default', DocumentationSidebar::create()->makeGroupTitle('default'));
    }

    public function testCanHaveMultipleGroupedPagesWithTheSameNameLabels()
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

    public function testDuplicateLabelsWithinTheSameGroupIsRemoved()
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

    public function testIsGroupActiveForIndexPageWithNoGroups()
    {
        $this->makePage('index');

        Render::setPage(DocumentationPage::get('index'));
        $this->assertFalse(DocumentationSidebar::create()->isGroupActive('foo'));
    }

    public function testIndexPageAddedToSidebarWhenItIsTheOnlyPage()
    {
        Filesystem::touch('_docs/index.md');
        $sidebar = DocumentationSidebar::create();

        $this->assertCount(1, $sidebar->items);
        $this->assertEquals(
            collect([NavItem::fromRoute(Routes::get('docs/index'))]),
            $sidebar->items
        );
    }

    public function testIndexPageNotAddedToSidebarWhenOtherPagesExist()
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
