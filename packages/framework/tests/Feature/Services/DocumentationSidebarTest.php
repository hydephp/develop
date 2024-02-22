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
use Hyde\Framework\Features\Navigation\NavigationMenuGenerator;

/**
 * @covers \Hyde\Framework\Features\Navigation\DocumentationSidebar
 * @covers \Hyde\Framework\Features\Navigation\NavigationMenuGenerator
 * @covers \Hyde\Framework\Features\Navigation\NavigationMenu
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
        $sidebar = NavigationMenuGenerator::handle(DocumentationSidebar::class);

        $this->assertInstanceOf(DocumentationSidebar::class, $sidebar);
    }

    public function testSidebarItemsAreAddedAutomatically()
    {
        $this->createTestFiles();

        $sidebar = NavigationMenuGenerator::handle(DocumentationSidebar::class);

        $this->assertCount(5, $sidebar->getItems());
    }

    public function testIndexPageIsRemovedFromSidebar()
    {
        $this->createTestFiles();
        Filesystem::touch('_docs/index.md');

        $sidebar = NavigationMenuGenerator::handle(DocumentationSidebar::class);
        $this->assertCount(5, $sidebar->getItems());
    }

    public function testFilesWithFrontMatterHiddenSetToTrueAreRemovedFromSidebar()
    {
        $this->createTestFiles();
        File::put(Hyde::path('_docs/test.md'), "---\nnavigation:\n    hidden: true\n---\n\n# Foo");

        $sidebar = NavigationMenuGenerator::handle(DocumentationSidebar::class);
        $this->assertCount(5, $sidebar->getItems());
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
            NavigationMenuGenerator::handle(DocumentationSidebar::class)->getItems()
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
            NavigationMenuGenerator::handle(DocumentationSidebar::class)->getItems()
        );
    }

    public function testSidebarItemPriorityCanBeSetInFrontMatter()
    {
        $this->makePage('foo', ['navigation.priority' => 25]);

        $this->assertEquals(25, NavigationMenuGenerator::handle(DocumentationSidebar::class)->getItems()->first()->getPriority());
    }

    public function testSidebarItemPrioritySetInConfigOverridesFrontMatter()
    {
        $this->makePage('foo', ['navigation.priority' => 25]);

        Config::set('docs.sidebar_order', ['foo']);

        $this->assertEquals(25, NavigationMenuGenerator::handle(DocumentationSidebar::class)->getItems()->first()->getPriority());
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
            NavigationMenuGenerator::handle(DocumentationSidebar::class)->getItems()
        );
    }

    public function testGroupCanBeSetInFrontMatter()
    {
        $this->makePage('foo', ['navigation.group' => 'bar']);
        $this->assertEquals('bar', collect(NavigationMenuGenerator::handle(DocumentationSidebar::class)->getItems()->first()->getChildren())->first()->getGroup());
    }

    public function testHasGroupsReturnsFalseWhenThereAreNoGroups()
    {
        $this->assertFalse(NavigationMenuGenerator::handle(DocumentationSidebar::class)->hasGroups());
    }

    public function testHasGroupsReturnsTrueWhenThereAreGroups()
    {
        $this->makePage('foo', ['navigation.group' => 'bar']);

        $this->assertTrue(NavigationMenuGenerator::handle(DocumentationSidebar::class)->hasGroups());
    }

    public function testHasGroupsReturnsTrueWhenThereAreMultipleGroups()
    {
        $this->makePage('foo', ['navigation.group' => 'bar']);
        $this->makePage('bar', ['navigation.group' => 'baz']);

        $this->assertTrue(NavigationMenuGenerator::handle(DocumentationSidebar::class)->hasGroups());
    }

    public function testHasGroupsReturnsTrueWhenThereAreMultipleGroupsMixedWithDefaults()
    {
        $this->makePage('foo', ['navigation.group' => 'bar']);
        $this->makePage('bar', ['navigation.group' => 'baz']);
        $this->makePage('baz');

        $this->assertTrue(NavigationMenuGenerator::handle(DocumentationSidebar::class)->hasGroups());
    }

    public function testGetItemsInGroupDoesNotIncludeDocsIndex()
    {
        Filesystem::touch('_docs/foo.md');
        Filesystem::touch('_docs/index.md');

        $this->assertEquals(
            collect([NavItem::fromRoute(Routes::get('docs/foo'), priority: 999)]),
            NavigationMenuGenerator::handle(DocumentationSidebar::class)->getItems()
        );
    }

    public function testIsGroupActiveReturnsFalseWhenSuppliedGroupIsNotActive()
    {
        Render::setPage(new DocumentationPage(matter: ['navigation.group' => 'foo']));
        $this->assertFalse(NavigationMenuGenerator::handle(DocumentationSidebar::class)->isGroupActive('bar'));
    }

    public function testIsGroupActiveReturnsTrueWhenSuppliedGroupIsActive()
    {
        Render::setPage(new DocumentationPage(matter: ['navigation.group' => 'foo']));
        $this->assertTrue(NavigationMenuGenerator::handle(DocumentationSidebar::class)->isGroupActive('foo'));
    }

    public function testIsGroupActiveReturnsTrueForDifferingCasing()
    {
        Render::setPage(new DocumentationPage(matter: ['navigation.group' => 'Foo Bar']));
        $this->assertTrue(NavigationMenuGenerator::handle(DocumentationSidebar::class)->isGroupActive('foo-bar'));
    }

    public function testIsGroupActiveReturnsTrueFirstGroupOfIndexPage()
    {
        $this->makePage('index');
        $this->makePage('foo', ['navigation.group' => 'foo']);
        $this->makePage('bar', ['navigation.group' => 'bar']);
        $this->makePage('baz', ['navigation.group' => 'baz']);

        Render::setPage(DocumentationPage::get('index'));
        $this->assertTrue(NavigationMenuGenerator::handle(DocumentationSidebar::class)->isGroupActive('bar'));
        $this->assertFalse(NavigationMenuGenerator::handle(DocumentationSidebar::class)->isGroupActive('foo'));
        $this->assertFalse(NavigationMenuGenerator::handle(DocumentationSidebar::class)->isGroupActive('baz'));
    }

    public function testIsGroupActiveReturnsTrueFirstSortedGroupOfIndexPage()
    {
        $this->makePage('index');
        $this->makePage('foo', ['navigation.group' => 'foo', 'navigation.priority' => 1]);
        $this->makePage('bar', ['navigation.group' => 'bar', 'navigation.priority' => 2]);
        $this->makePage('baz', ['navigation.group' => 'baz', 'navigation.priority' => 3]);

        Render::setPage(DocumentationPage::get('index'));
        $this->assertTrue(NavigationMenuGenerator::handle(DocumentationSidebar::class)->isGroupActive('foo'));
        $this->assertFalse(NavigationMenuGenerator::handle(DocumentationSidebar::class)->isGroupActive('bar'));
        $this->assertFalse(NavigationMenuGenerator::handle(DocumentationSidebar::class)->isGroupActive('baz'));
    }

    public function testAutomaticIndexPageGroupExpansionRespectsCustomNavigationMenuSettings()
    {
        $this->makePage('index', ['navigation.group' => 'baz']);
        $this->makePage('foo', ['navigation.group' => 'foo', 'navigation.priority' => 1]);
        $this->makePage('bar', ['navigation.group' => 'bar', 'navigation.priority' => 2]);
        $this->makePage('baz', ['navigation.group' => 'baz', 'navigation.priority' => 3]);

        Render::setPage(DocumentationPage::get('index'));
        $this->assertFalse(NavigationMenuGenerator::handle(DocumentationSidebar::class)->isGroupActive('foo'));
        $this->assertFalse(NavigationMenuGenerator::handle(DocumentationSidebar::class)->isGroupActive('bar'));
        $this->assertTrue(NavigationMenuGenerator::handle(DocumentationSidebar::class)->isGroupActive('baz'));
    }

    public function testCanHaveMultipleGroupedPagesWithTheSameNameLabels()
    {
        $this->makePage('foo', ['navigation.group' => 'foo', 'navigation.label' => 'Foo']);
        $this->makePage('bar', ['navigation.group' => 'bar', 'navigation.label' => 'Foo']);

        $sidebar = NavigationMenuGenerator::handle(DocumentationSidebar::class);
        $this->assertCount(2, $sidebar->getItems());

        $this->assertEquals(
            collect([
                NavItem::dropdown('Bar', [
                    NavItem::fromRoute(Routes::get('docs/bar'), priority: 999),
                ]),
                NavItem::dropdown('Foo', [
                    NavItem::fromRoute(Routes::get('docs/foo'), priority: 999),
                ]),
            ]),
            $sidebar->getItems()
        );
    }

    public function testDuplicateLabelsWithinTheSameGroupAreNotRemoved()
    {
        $this->makePage('foo', ['navigation.group' => 'foo', 'navigation.label' => 'Foo']);
        $this->makePage('bar', ['navigation.group' => 'foo', 'navigation.label' => 'Foo']);

        $sidebar = NavigationMenuGenerator::handle(DocumentationSidebar::class);
        $this->assertCount(1, $sidebar->getItems());

        $this->assertEquals(
            collect([
                NavItem::dropdown('Foo', [
                    NavItem::fromRoute(Routes::get('docs/bar'), priority: 999),
                    NavItem::fromRoute(Routes::get('docs/foo'), priority: 999),
                ]),
            ]),
            $sidebar->getItems()
        );
    }

    public function testIsGroupActiveForIndexPageWithNoGroups()
    {
        $this->makePage('index');

        Render::setPage(DocumentationPage::get('index'));
        $this->assertFalse(NavigationMenuGenerator::handle(DocumentationSidebar::class)->isGroupActive('foo'));
    }

    public function testIndexPageAddedToSidebarWhenItIsTheOnlyPage()
    {
        Filesystem::touch('_docs/index.md');
        $sidebar = NavigationMenuGenerator::handle(DocumentationSidebar::class);

        $this->assertCount(1, $sidebar->getItems());
        $this->assertEquals(
            collect([NavItem::fromRoute(Routes::get('docs/index'))]),
            $sidebar->getItems()
        );
    }

    public function testIndexPageNotAddedToSidebarWhenOtherPagesExist()
    {
        $this->createTestFiles(1);
        Filesystem::touch('_docs/index.md');
        $sidebar = NavigationMenuGenerator::handle(DocumentationSidebar::class);

        $this->assertCount(1, $sidebar->getItems());
        $this->assertEquals(
            collect([NavItem::fromRoute(Routes::get('docs/test-0'))]),
            $sidebar->getItems()
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
