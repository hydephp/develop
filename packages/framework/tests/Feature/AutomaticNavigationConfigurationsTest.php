<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Pages\HtmlPage;
use Hyde\Pages\BladePage;
use Hyde\Testing\TestCase;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Pages\InMemoryPage;
use Hyde\Foundation\HydeKernel;
use JetBrains\PhpStorm\NoReturn;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Models\Redirect;
use Illuminate\Support\Collection;
use Hyde\Foundation\Kernel\RouteCollection;
use Hyde\Framework\Features\Navigation\NavItem;
use Hyde\Framework\Features\Navigation\GeneratesMainNavigationMenu;
use Hyde\Framework\Features\Navigation\GeneratesDocumentationSidebarMenu;

/**
 * High-level broad-spectrum tests for the automatic navigation configurations, testing various setups.
 *
 * @covers \Hyde\Framework\Factories\NavigationDataFactory
 * @covers \Hyde\Framework\Features\Navigation\DocumentationSidebar
 * @covers \Hyde\Framework\Features\Navigation\GeneratesDocumentationSidebarMenu
 * @covers \Hyde\Framework\Features\Navigation\GeneratesMainNavigationMenu
 * @covers \Hyde\Framework\Features\Navigation\NavItem
 */
class AutomaticNavigationConfigurationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->kernel = new TestKernel();
        HydeKernel::setInstance($this->kernel);
    }

    // Base tests

    public function testMainNavigationMenu()
    {
        $this->menu()->assertEquals(['Home']);
    }

    public function testDocumentationSidebarMenu()
    {
        $this->sidebar()->assertEquals([]);
    }

    // Main navigation menu tests

    public function testMainNavigationMenuWithPages()
    {
        $this->withPages([
            new MarkdownPage('about'),
            new MarkdownPage('contact'),
        ])->menu()->assertEquals([
            'About',
            'Contact',
        ]);
    }

    public function testOnlyRootTypePagesAreAddedToNavigationMenu()
    {
        $this->assertMenuEquals(['Html Page', 'Blade Page', 'Markdown Page'], [
            new HtmlPage('html-page'),
            new BladePage('blade-page'),
            new MarkdownPage('markdown-page'),
            new MarkdownPost('markdown-post'),
            new DocumentationPage('documentation-page'),
        ]);
    }

    public function testDocumentationIndexPagesAreAddedToNavigationMenu()
    {
        $this->assertMenuEquals(['Docs'], [
            new DocumentationPage('index'),
            new DocumentationPage('foo'),
        ]);
    }

    public function testInMemoryPagesAreAddedToNavigationMenu()
    {
        $this->assertMenuEquals(['In Memory Page'], [
            new InMemoryPage('in-memory-page'),
        ]);
    }

    public function testMainNavigationDoesNotInclude404Page()
    {
        $this->assertMenuEquals([], [new MarkdownPage('404')]);
    }

    public function testRedirectPagesAreAddedToNavigationMenu()
    {
        $this->assertMenuEquals(['Redirect'], [
            new Redirect('redirect', 'destination'),
        ]);
    }

    // Main navigation front matter tests

    public function testDefaultNavigationPriorities()
    {
        $this->assertMenuEquals([
            ['priority' => 0],
            ['priority' => 10],
            ['priority' => 100],
        ], [
            new MarkdownPage('index'),
            new MarkdownPage('posts'),
            new MarkdownPage('docs/index'),
        ]);
    }

    public function testDefaultNavigationLabels()
    {
        $this->assertMenuEquals([
            ['label' => 'Home'],
            ['label' => 'Docs'],
        ], [
            new MarkdownPage('index'),
            new MarkdownPage('docs/index'),
        ]);
    }

    public function testMainNavigationMenuWithFrontMatterPriority()
    {
        $this->assertMenuEquals(['First', 'Second', 'Third'], [
            new MarkdownPage('first', ['navigation.priority' => 1]),
            new MarkdownPage('second', ['navigation.priority' => 2]),
            new MarkdownPage('third', ['navigation.priority' => 3]),
        ]);

        $this->assertMenuEquals(['Third', 'Second', 'First'], [
            new MarkdownPage('first', ['navigation.priority' => 3]),
            new MarkdownPage('second', ['navigation.priority' => 2]),
            new MarkdownPage('third', ['navigation.priority' => 1]),
        ]);
    }

    public function testMainNavigationMenuWithFrontMatterOrder()
    {
        $this->assertMenuEquals(['First', 'Second', 'Third'], [
            new MarkdownPage('first', ['navigation.order' => 1]),
            new MarkdownPage('second', ['navigation.order' => 2]),
            new MarkdownPage('third', ['navigation.order' => 3]),
        ]);

        $this->assertMenuEquals(['Third', 'Second', 'First'], [
            new MarkdownPage('first', ['navigation.order' => 3]),
            new MarkdownPage('second', ['navigation.order' => 2]),
            new MarkdownPage('third', ['navigation.order' => 1]),
        ]);
    }

    public function testMainNavigationMenuWithFrontMatterLabel()
    {
        $this->assertMenuEquals(['First', 'Second', 'Third'], [
            new MarkdownPage('foo', ['navigation.label' => 'First']),
            new MarkdownPage('bar', ['navigation.label' => 'Second']),
            new MarkdownPage('baz', ['navigation.label' => 'Third']),
        ]);
    }

    public function testMainNavigationMenuWithFrontMatterHidden()
    {
        $this->assertMenuEquals(['Foo', 'Bar', 'Baz'], [
            new MarkdownPage('foo', ['navigation.hidden' => false]),
            new MarkdownPage('bar', ['navigation.hidden' => false]),
            new MarkdownPage('baz', ['navigation.hidden' => false]),
        ]);

        $this->assertMenuEquals([], [
            new MarkdownPage('foo', ['navigation.hidden' => true]),
            new MarkdownPage('bar', ['navigation.hidden' => true]),
            new MarkdownPage('baz', ['navigation.hidden' => true]),
        ]);
    }

    public function testMainNavigationMenuWithFrontMatterVisible()
    {
        $this->assertMenuEquals(['Foo', 'Bar', 'Baz'], [
            new MarkdownPage('foo', ['navigation.visible' => true]),
            new MarkdownPage('bar', ['navigation.visible' => true]),
            new MarkdownPage('baz', ['navigation.visible' => true]),
        ]);

        $this->assertMenuEquals([], [
            new MarkdownPage('foo', ['navigation.visible' => false]),
            new MarkdownPage('bar', ['navigation.visible' => false]),
            new MarkdownPage('baz', ['navigation.visible' => false]),
        ]);
    }

    public function testMainNavigationMenuWithFrontMatterGroup()
    {
        // TODO: For new v2 system, this should insert a root item with the group name and the children as the pages

        $this->assertMenuEquals([
            ['label' => 'Foo', 'group' => 'group-1'],
            ['label' => 'Bar', 'group' => 'group-1'],
            ['label' => 'Baz', 'group' => 'group-1'],
        ], [
            new MarkdownPage('foo', ['navigation.group' => 'Group 1']),
            new MarkdownPage('bar', ['navigation.group' => 'Group 1']),
            new MarkdownPage('baz', ['navigation.group' => 'Group 1']),
        ]);
    }

    public function testMainNavigationMenuWithFrontMatterCategory()
    {
        // TODO: For new v2 system, this should insert a root item with the group name and the children as the pages

        $this->assertMenuEquals([
            ['label' => 'Foo', 'group' => 'group-1'],
            ['label' => 'Bar', 'group' => 'group-1'],
            ['label' => 'Baz', 'group' => 'group-1'],
        ], [
            new MarkdownPage('foo', ['navigation.category' => 'Group 1']),
            new MarkdownPage('bar', ['navigation.category' => 'Group 1']),
            new MarkdownPage('baz', ['navigation.category' => 'Group 1']),
        ]);
    }

    public function testMainNavigationMenuWithFrontMatterPriorityAndOrder()
    {
        // Since the main key in the navigation schema is 'priority', that takes precedence over its 'order' alias

        $expected = [
            ['label' => 'Foo', 'priority' => 1],
            ['label' => 'Bar', 'priority' => 2],
            ['label' => 'Baz', 'priority' => 3],
        ];

        $this->assertMenuEquals($expected, [
            new MarkdownPage('foo', ['navigation.priority' => 1, 'navigation.order' => 10]),
            new MarkdownPage('bar', ['navigation.priority' => 2, 'navigation.order' => 20]),
            new MarkdownPage('baz', ['navigation.priority' => 3, 'navigation.order' => 30]),
        ]);

        $this->assertMenuEquals($expected, [
            new MarkdownPage('foo', ['navigation.order' => 10, 'navigation.priority' => 1]),
            new MarkdownPage('bar', ['navigation.order' => 20, 'navigation.priority' => 2]),
            new MarkdownPage('baz', ['navigation.order' => 30, 'navigation.priority' => 3]),
        ]);
    }

    public function testMainNavigationMenuWithFrontMatterHiddenAndVisible()
    {
        // Since the main key in the navigation schema is 'hidden', that takes precedence over its 'visible' alias

        $this->assertMenuEquals(['Foo', 'Bar', 'Baz'], [
            new MarkdownPage('foo', ['navigation.hidden' => false, 'navigation.visible' => true]),
            new MarkdownPage('bar', ['navigation.hidden' => false, 'navigation.visible' => true]),
            new MarkdownPage('baz', ['navigation.hidden' => false, 'navigation.visible' => true]),
        ]);

        $this->assertMenuEquals([], [
            new MarkdownPage('foo', ['navigation.hidden' => true, 'navigation.visible' => false]),
            new MarkdownPage('bar', ['navigation.hidden' => true, 'navigation.visible' => false]),
            new MarkdownPage('baz', ['navigation.hidden' => true, 'navigation.visible' => false]),
        ]);

        $this->assertMenuEquals([], [
            new MarkdownPage('foo', ['navigation.hidden' => true, 'navigation.visible' => true]),
            new MarkdownPage('bar', ['navigation.hidden' => true, 'navigation.visible' => true]),
            new MarkdownPage('baz', ['navigation.hidden' => true, 'navigation.visible' => true]),
        ]);

        $this->assertMenuEquals(['Foo', 'Bar', 'Baz'], [
            new MarkdownPage('foo', ['navigation.hidden' => false, 'navigation.visible' => false]),
            new MarkdownPage('bar', ['navigation.hidden' => false, 'navigation.visible' => false]),
            new MarkdownPage('baz', ['navigation.hidden' => false, 'navigation.visible' => false]),
        ]);

        $this->assertMenuEquals(['Bar'], [
            new MarkdownPage('foo', ['navigation.hidden' => true, 'navigation.visible' => false]),
            new MarkdownPage('bar', ['navigation.hidden' => false, 'navigation.visible' => true]),
            new MarkdownPage('baz', ['navigation.hidden' => true, 'navigation.visible' => false]),
        ]);
    }

    public function testMainNavigationMenuWithFrontMatterGroupAndCategory()
    {
        // Since the main key in the navigation schema is 'group', that takes precedence over its 'category' alias

        $this->assertMenuEquals(array_fill(0, 3, ['group' => 'group-1']), [
            new MarkdownPage('foo', ['navigation.group' => 'Group 1', 'navigation.category' => 'Group 2']),
            new MarkdownPage('bar', ['navigation.group' => 'Group 1', 'navigation.category' => 'Group 2']),
            new MarkdownPage('baz', ['navigation.group' => 'Group 1', 'navigation.category' => 'Group 2']),
        ]);
    }

    // Main navigation configuration tests

    public function testMainNavigationMenuWithConfigOrder()
    {
        config(['hyde.navigation.order' => ['first', 'second', 'third']]);

        $this->assertMenuEquals(['First', 'Second', 'Third'], [
            new MarkdownPage('first'),
            new MarkdownPage('second'),
            new MarkdownPage('third'),
        ]);

        config(['hyde.navigation.order' => ['third', 'second', 'first']]);

        $this->assertMenuEquals(['Third', 'Second', 'First'], [
            new MarkdownPage('first'),
            new MarkdownPage('second'),
            new MarkdownPage('third'),
        ]);
    }

    public function testMainNavigationMenuWithConfigOrderHasInferredPriorities()
    {
        $this->assertMenuEquals([
            ['priority' => 999],
            ['priority' => 999],
            ['priority' => 999],
        ], [
            new MarkdownPage('first'),
            new MarkdownPage('second'),
            new MarkdownPage('third'),
        ]);

        config(['hyde.navigation.order' => ['first', 'second', 'third']]);

        $this->assertMenuEquals([
            ['priority' => 500],
            ['priority' => 501],
            ['priority' => 502],
        ], [
            new MarkdownPage('first'),
            new MarkdownPage('second'),
            new MarkdownPage('third'),
        ]);
    }

    public function testMainNavigationMenuWithExplicitConfigOrder()
    {
        config(['hyde.navigation.order' => ['first' => 1, 'second' => 2, 'third' => 3]]);

        $this->assertMenuEquals(['First', 'Second', 'Third'], [
            new MarkdownPage('first'),
            new MarkdownPage('second'),
            new MarkdownPage('third'),
        ]);

        config(['hyde.navigation.order' => ['first' => 3, 'second' => 2, 'third' => 1]]);

        $this->assertMenuEquals(['Third', 'Second', 'First'], [
            new MarkdownPage('first'),
            new MarkdownPage('second'),
            new MarkdownPage('third'),
        ]);

        config(['hyde.navigation.order' => ['first' => 1, 'second' => 2, 'third' => 3]]);

        $this->assertMenuEquals([
            ['label' => 'First', 'priority' => 1],
            ['label' => 'Second', 'priority' => 2],
            ['label' => 'Third', 'priority' => 3],
        ], [
            new MarkdownPage('first'),
            new MarkdownPage('second'),
            new MarkdownPage('third'),
        ]);
    }

    public function testMainNavigationMenuWithMixedConfigOrders()
    {
        config(['hyde.navigation.order' => ['foo', 'bar' => 650]]);

        $this->assertMenuEquals([
            ['label' => 'Foo', 'priority' => 500],
            ['label' => 'Bar', 'priority' => 650],
            ['label' => 'Baz', 'priority' => 999],
        ], [
            new MarkdownPage('foo'),
            new MarkdownPage('bar'),
            new MarkdownPage('baz'),
        ]);
    }

    public function testMainNavigationMenuWithConfigLabels()
    {
        config(['hyde.navigation.labels' => ['foo' => 'First', 'bar' => 'Second', 'baz' => 'Third']]);

        $this->assertMenuEquals(['First', 'Second', 'Third'], [
            new MarkdownPage('foo'),
            new MarkdownPage('bar'),
            new MarkdownPage('baz'),
        ]);
    }

    public function testMainNavigationDropdownLabelsCanBeSetInConfig()
    {
        $this->markTestSkipped('Not yet implemented');
    }

    public function testMainNavigationAutomaticDropdownLabelsCanBeSetInConfig()
    {
        $this->markTestSkipped('Not yet implemented');
    }

    public function testMainNavigationMenuWithConfigHidden()
    {
        config(['hyde.navigation.exclude' => ['foo', 'bar', 'baz']]);

        $this->assertMenuEquals([], [
            new MarkdownPage('foo'),
            new MarkdownPage('bar'),
            new MarkdownPage('baz'),
        ]);
    }

    // Main navigation subdirectory handling tests

    public function testPagesInSubdirectoriesAreNotAddedToNavigation()
    {
        $this->assertMenuEquals([], [
            new MarkdownPage('about/foo'),
            new MarkdownPage('about/bar'),
            new MarkdownPage('about/baz'),
        ]);
    }

    public function testPagesInSubdirectoriesAreAddedToNavigationWhenNavigationSubdirectoriesIsSetToFlat()
    {
        config(['hyde.navigation.subdirectories' => 'flat']);

        $this->assertMenuEquals(['Foo', 'Bar', 'Baz'], [
            new MarkdownPage('about/foo'),
            new MarkdownPage('about/bar'),
            new MarkdownPage('about/baz'),
        ]);
    }

    public function testPagesInSubdirectoriesAreAddedAsDropdownsWhenNavigationSubdirectoriesIsSetToDropdown()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);

        $this->assertMenuEquals([
            ['label' => 'about', 'children' => ['Foo', 'Bar', 'Baz']],
            // TODO: Label should be About
        ], [
            new MarkdownPage('about/foo'),
            new MarkdownPage('about/bar'),
            new MarkdownPage('about/baz'),
        ]);
    }

    public function testMainNavigationMenuItemsWithTheSameLabelAreNotFilteredForDuplicates()
    {
        $this->assertMenuEquals(['Foo', 'Foo'], [
            new MarkdownPage('foo', ['navigation.label' => 'Foo']),
            new MarkdownPage('bar', ['navigation.label' => 'Foo']),
        ]);
    }

    public function testMainNavigationMenuItemsWithTheSameLabelAreNotFilteredForDuplicatesRegardlessOfCase()
    {
        $this->assertMenuEquals(['Foo', 'Foo', 'FOO'], [
            new MarkdownPage('foo'),
            new MarkdownPage('Foo'),
            new MarkdownPage('FOO'),
        ]);

        $this->assertMenuEquals(['foo', 'Foo', 'FOO'], [
            new MarkdownPage('foo', ['navigation.label' => 'foo']),
            new MarkdownPage('bar', ['navigation.label' => 'Foo']),
            new MarkdownPage('baz', ['navigation.label' => 'FOO']),
        ]);
    }

    public function testMainNavigationMenuItemsWithSameLabelButDifferentGroupsAreNotFiltered()
    {
        $this->assertMenuEquals([
            ['label' => 'Foo', 'group' => 'group-1'],
            ['label' => 'Foo', 'group' => 'group-2'],
        ], [
            new MarkdownPage('foo', ['navigation.label' => 'Foo', 'navigation.group' => 'Group 1']),
            new MarkdownPage('bar', ['navigation.label' => 'Foo', 'navigation.group' => 'Group 2']),
        ]);
    }

    public function testMainNavigationMenuDropdownItemsWithSameLabelButDifferentGroupsAreNotFiltered()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);

        $this->assertMenuEquals([
            // Todo: Should use proper group name
            ['label' => 'group-1', 'children' => ['Foo']],
            ['label' => 'group-2', 'children' => ['Foo']],
        ], [
            new MarkdownPage('one/foo', ['navigation.group' => 'Group 1']),
            new MarkdownPage('two/foo', ['navigation.group' => 'Group 2']),
        ]);
    }

    public function testMainNavigationMenuAutomaticDropdownItemsWithSameLabelButDifferentGroupsAreNotFiltered()
    {
        config(['hyde.navigation.subdirectories' => 'dropdown']);

        $this->assertMenuEquals([
            // Todo: Should use proper group name
            ['label' => 'one', 'children' => ['Foo']],
            ['label' => 'two', 'children' => ['Foo']],
        ], [
            new MarkdownPage('one/foo'),
            new MarkdownPage('two/foo'),
        ]);
    }

    // Documentation sidebar menu tests

    public function testSidebarWithPages()
    {
        $this->assertSidebarEquals(['Foo', 'Bar', 'Baz'], [
            new DocumentationPage('foo'),
            new DocumentationPage('bar'),
            new DocumentationPage('baz'),
        ]);
    }

    public function testOnlyDocumentationPagesAreAddedToSidebar()
    {
        $this->assertSidebarEquals(['Documentation Page'], [
            new HtmlPage('html-page'),
            new BladePage('blade-page'),
            new MarkdownPage('markdown-page'),
            new MarkdownPost('markdown-post'),
            new DocumentationPage('documentation-page'),
            new InMemoryPage('in-memory-page'),
            new Redirect('redirect', 'destination'),
        ]);
    }

    // Documentation sidebar front matter tests

    public function testSidebarWithFrontMatterPriority()
    {
        $this->assertSidebarEquals(['First', 'Second', 'Third'], [
            new DocumentationPage('first', ['navigation.priority' => 1]),
            new DocumentationPage('second', ['navigation.priority' => 2]),
            new DocumentationPage('third', ['navigation.priority' => 3]),
        ]);

        $this->assertSidebarEquals(['Third', 'Second', 'First'], [
            new DocumentationPage('first', ['navigation.priority' => 3]),
            new DocumentationPage('second', ['navigation.priority' => 2]),
            new DocumentationPage('third', ['navigation.priority' => 1]),
        ]);
    }

    public function testSidebarWithFrontMatterOrder()
    {
        $this->assertSidebarEquals(['First', 'Second', 'Third'], [
            new DocumentationPage('first', ['navigation.order' => 1]),
            new DocumentationPage('second', ['navigation.order' => 2]),
            new DocumentationPage('third', ['navigation.order' => 3]),
        ]);

        $this->assertSidebarEquals(['Third', 'Second', 'First'], [
            new DocumentationPage('first', ['navigation.order' => 3]),
            new DocumentationPage('second', ['navigation.order' => 2]),
            new DocumentationPage('third', ['navigation.order' => 1]),
        ]);
    }

    public function testSidebarWithFrontMatterLabel()
    {
        $this->assertSidebarEquals(['First', 'Second', 'Third'], [
            new DocumentationPage('foo', ['navigation.label' => 'First']),
            new DocumentationPage('bar', ['navigation.label' => 'Second']),
            new DocumentationPage('baz', ['navigation.label' => 'Third']),
        ]);
    }

    public function testSidebarWithFrontMatterHidden()
    {
        $this->assertSidebarEquals(['Foo', 'Bar', 'Baz'], [
            new DocumentationPage('foo', ['navigation.hidden' => false]),
            new DocumentationPage('bar', ['navigation.hidden' => false]),
            new DocumentationPage('baz', ['navigation.hidden' => false]),
        ]);

        $this->assertSidebarEquals([], [
            new DocumentationPage('foo', ['navigation.hidden' => true]),
            new DocumentationPage('bar', ['navigation.hidden' => true]),
            new DocumentationPage('baz', ['navigation.hidden' => true]),
        ]);
    }

    public function testSidebarWithFrontMatterVisible()
    {
        $this->assertSidebarEquals(['Foo', 'Bar', 'Baz'], [
            new DocumentationPage('foo', ['navigation.visible' => true]),
            new DocumentationPage('bar', ['navigation.visible' => true]),
            new DocumentationPage('baz', ['navigation.visible' => true]),
        ]);

        $this->assertSidebarEquals([], [
            new DocumentationPage('foo', ['navigation.visible' => false]),
            new DocumentationPage('bar', ['navigation.visible' => false]),
            new DocumentationPage('baz', ['navigation.visible' => false]),
        ]);
    }

    public function testSidebarWithFrontMatterGroup()
    {
        // TODO: For new v2 system, this should insert a root item with the group name and the children as the pages

        $this->assertSidebarEquals([
            ['label' => 'Foo', 'group' => 'group-1'],
            ['label' => 'Bar', 'group' => 'group-1'],
            ['label' => 'Baz', 'group' => 'group-1'],
        ], [
            new DocumentationPage('foo', ['navigation.group' => 'Group 1']),
            new DocumentationPage('bar', ['navigation.group' => 'Group 1']),
            new DocumentationPage('baz', ['navigation.group' => 'Group 1']),
        ]);
    }

    public function testSidebarWithFrontMatterCategory()
    {
        // TODO: For new v2 system, this should insert a root item with the group name and the children as the pages

        $this->assertSidebarEquals([
            ['label' => 'Foo', 'group' => 'group-1'],
            ['label' => 'Bar', 'group' => 'group-1'],
            ['label' => 'Baz', 'group' => 'group-1'],
        ], [
            new DocumentationPage('foo', ['navigation.category' => 'Group 1']),
            new DocumentationPage('bar', ['navigation.category' => 'Group 1']),
            new DocumentationPage('baz', ['navigation.category' => 'Group 1']),
        ]);
    }

    public function testSidebarWithFrontMatterPriorityAndOrder()
    {
        // Since the main key in the navigation schema is 'priority', that takes precedence over its 'order' alias

        $expected = [
            ['label' => 'Foo', 'priority' => 1],
            ['label' => 'Bar', 'priority' => 2],
            ['label' => 'Baz', 'priority' => 3],
        ];

        $this->assertSidebarEquals($expected, [
            new DocumentationPage('foo', ['navigation.priority' => 1, 'navigation.order' => 10]),
            new DocumentationPage('bar', ['navigation.priority' => 2, 'navigation.order' => 20]),
            new DocumentationPage('baz', ['navigation.priority' => 3, 'navigation.order' => 30]),
        ]);

        $this->assertSidebarEquals($expected, [
            new DocumentationPage('foo', ['navigation.order' => 10, 'navigation.priority' => 1]),
            new DocumentationPage('bar', ['navigation.order' => 20, 'navigation.priority' => 2]),
            new DocumentationPage('baz', ['navigation.order' => 30, 'navigation.priority' => 3]),
        ]);
    }

    public function testSidebarWithFrontMatterHiddenAndVisible()
    {
        // Since the main key in the navigation schema is 'hidden', that takes precedence over its 'visible' alias

        $this->assertSidebarEquals(['Foo', 'Bar', 'Baz'], [
            new DocumentationPage('foo', ['navigation.hidden' => false, 'navigation.visible' => true]),
            new DocumentationPage('bar', ['navigation.hidden' => false, 'navigation.visible' => true]),
            new DocumentationPage('baz', ['navigation.hidden' => false, 'navigation.visible' => true]),
        ]);

        $this->assertSidebarEquals([], [
            new DocumentationPage('foo', ['navigation.hidden' => true, 'navigation.visible' => false]),
            new DocumentationPage('bar', ['navigation.hidden' => true, 'navigation.visible' => false]),
            new DocumentationPage('baz', ['navigation.hidden' => true, 'navigation.visible' => false]),
        ]);

        $this->assertSidebarEquals([], [
            new DocumentationPage('foo', ['navigation.hidden' => true, 'navigation.visible' => true]),
            new DocumentationPage('bar', ['navigation.hidden' => true, 'navigation.visible' => true]),
            new DocumentationPage('baz', ['navigation.hidden' => true, 'navigation.visible' => true]),
        ]);

        $this->assertSidebarEquals(['Foo', 'Bar', 'Baz'], [
            new DocumentationPage('foo', ['navigation.hidden' => false, 'navigation.visible' => false]),
            new DocumentationPage('bar', ['navigation.hidden' => false, 'navigation.visible' => false]),
            new DocumentationPage('baz', ['navigation.hidden' => false, 'navigation.visible' => false]),
        ]);

        $this->assertSidebarEquals(['Bar'], [
            new DocumentationPage('foo', ['navigation.hidden' => true, 'navigation.visible' => false]),
            new DocumentationPage('bar', ['navigation.hidden' => false, 'navigation.visible' => true]),
            new DocumentationPage('baz', ['navigation.hidden' => true, 'navigation.visible' => false]),
        ]);
    }

    public function testSidebarWithFrontMatterGroupAndCategory()
    {
        // Since the main key in the navigation schema is 'group', that takes precedence over its 'category' alias

        $this->assertSidebarEquals(array_fill(0, 3, ['group' => 'group-1']), [
            new DocumentationPage('foo', ['navigation.group' => 'Group 1', 'navigation.category' => 'Group 2']),
            new DocumentationPage('bar', ['navigation.group' => 'Group 1', 'navigation.category' => 'Group 2']),
            new DocumentationPage('baz', ['navigation.group' => 'Group 1', 'navigation.category' => 'Group 2']),
        ]);
    }

    // Sidebar configuration tests

    public function testSidebarWithConfigOrder()
    {
        // TODO should be sidebar.order instead of docs.sidebar_order

        config(['docs.sidebar_order' => ['first', 'second', 'third']]);

        $this->assertSidebarEquals(['First', 'Second', 'Third'], [
            new DocumentationPage('first'),
            new DocumentationPage('second'),
            new DocumentationPage('third'),
        ]);

        config(['docs.sidebar_order' => ['third', 'second', 'first']]);

        $this->assertSidebarEquals(['Third', 'Second', 'First'], [
            new DocumentationPage('first'),
            new DocumentationPage('second'),
            new DocumentationPage('third'),
        ]);
    }

    public function testSidebarWithConfigOrderHasInferredPriorities()
    {
        $this->assertSidebarEquals([
            ['priority' => 999],
            ['priority' => 999],
            ['priority' => 999],
        ], [
            new DocumentationPage('first'),
            new DocumentationPage('second'),
            new DocumentationPage('third'),
        ]);

        config(['docs.sidebar_order' => ['first', 'second', 'third']]);

        $this->assertSidebarEquals([
            ['priority' => 500],
            ['priority' => 501],
            ['priority' => 502],
        ], [
            new DocumentationPage('first'),
            new DocumentationPage('second'),
            new DocumentationPage('third'),
        ]);
    }

    public function testSidebarWithExplicitConfigOrder()
    {
        config(['docs.sidebar_order' => ['first' => 1, 'second' => 2, 'third' => 3]]);

        $this->assertSidebarEquals(['First', 'Second', 'Third'], [
            new DocumentationPage('first'),
            new DocumentationPage('second'),
            new DocumentationPage('third'),
        ]);

        config(['docs.sidebar_order' => ['first' => 3, 'second' => 2, 'third' => 1]]);

        $this->assertSidebarEquals(['Third', 'Second', 'First'], [
            new DocumentationPage('first'),
            new DocumentationPage('second'),
            new DocumentationPage('third'),
        ]);

        config(['docs.sidebar_order' => ['first' => 1, 'second' => 2, 'third' => 3]]);

        $this->assertSidebarEquals([
            ['label' => 'First', 'priority' => 1],
            ['label' => 'Second', 'priority' => 2],
            ['label' => 'Third', 'priority' => 3],
        ], [
            new DocumentationPage('first'),
            new DocumentationPage('second'),
            new DocumentationPage('third'),
        ]);
    }

    public function testSidebarWithMixedConfigOrders()
    {
        config(['docs.sidebar_order' => ['foo', 'bar' => 650]]);

        $this->assertSidebarEquals([
            ['label' => 'Foo', 'priority' => 500],
            ['label' => 'Bar', 'priority' => 650],
            ['label' => 'Baz', 'priority' => 999],
        ], [
            new DocumentationPage('foo'),
            new DocumentationPage('bar'),
            new DocumentationPage('baz'),
        ]);
    }

    public function testSidebarWithConfigLabels()
    {
        $this->markTestSkipped('Not supported (yet?)');

        config(['docs.sidebar.labels' => ['foo' => 'First', 'bar' => 'Second', 'baz' => 'Third']]);

        $this->assertSidebarEquals(['First', 'Second', 'Third'], [
            new DocumentationPage('foo'),
            new DocumentationPage('bar'),
            new DocumentationPage('baz'),
        ]);
    }

    public function testSidebarDropdownLabelsCanBeSetInConfig()
    {
        $this->markTestSkipped('Not yet implemented');
    }

    public function testSidebarAutomaticDropdownLabelsCanBeSetInConfig()
    {
        $this->markTestSkipped('Not yet implemented');
    }

    public function testSidebarWithConfigHidden()
    {
        $this->markTestSkipped('Not supported (yet?)');

        config(['docs.sidebar.exclude' => ['foo', 'bar', 'baz']]);

        $this->assertSidebarEquals([], [
            new DocumentationPage('foo'),
            new DocumentationPage('bar'),
            new DocumentationPage('baz'),
        ]);
    }

    // Sidebar subdirectory handling tests

    public function testDocumentationPagesInSubdirectoriesAreAddedToSidebar()
    {
        $this->assertSidebarEquals([
            ['label' => 'Foo', 'group' => 'about'],
            ['label' => 'Bar', 'group' => 'about'],
            ['label' => 'Baz', 'group' => 'about'],
        ], [
            new DocumentationPage('about/foo'),
            new DocumentationPage('about/bar'),
            new DocumentationPage('about/baz'),
        ]);
    }

    public function testPagesInSubdirectoriesAreAddedToSidebarRegardlessOfConfiguration()
    {
        $options = ['dropdown', 'flat', 'hidden'];

        foreach ($options as $option) {
            config(['docs.sidebar.subdirectories' => $option]);

            $this->assertSidebarEquals([
                ['label' => 'Foo', 'group' => 'about'],
                ['label' => 'Bar', 'group' => 'about'],
                ['label' => 'Baz', 'group' => 'about'],
            ], [
                new DocumentationPage('about/foo'),
                new DocumentationPage('about/bar'),
                new DocumentationPage('about/baz'),
            ]);
        }
    }

    public function testSidebarItemsWithTheSameLabelAreNotFiltered()
    {
        $this->assertSidebarEquals(['Foo', 'Foo'], [
            new DocumentationPage('foo', ['navigation.label' => 'Foo']),
            new DocumentationPage('bar', ['navigation.label' => 'Foo']),
        ]);
    }

    public function testSidebarItemsWithTheSameLabelAreNotFilteredForDuplicatesRegardlessOfCase()
    {
        $this->assertSidebarEquals(['Foo', 'Foo', 'FOO'], [
            new DocumentationPage('foo'),
            new DocumentationPage('Foo'),
            new DocumentationPage('FOO'),
        ]);

        $this->assertSidebarEquals(['foo', 'Foo', 'FOO'], [
            new DocumentationPage('foo', ['navigation.label' => 'foo']),
            new DocumentationPage('bar', ['navigation.label' => 'Foo']),
            new DocumentationPage('baz', ['navigation.label' => 'FOO']),
        ]);
    }

    public function testSidebarItemsWithSameLabelButDifferentGroupsAreNotFiltered()
    {
        $this->assertSidebarEquals([
            ['label' => 'Foo', 'group' => 'group-1'],
            ['label' => 'Foo', 'group' => 'group-2'],
        ], [
            new DocumentationPage('foo', ['navigation.label' => 'Foo', 'navigation.group' => 'Group 1']),
            new DocumentationPage('bar', ['navigation.label' => 'Foo', 'navigation.group' => 'Group 2']),
        ]);
    }

    public function testSidebarDropdownItemsWithSameLabelButDifferentGroupsAreFiltered()
    {
        $this->assertSidebarEquals(['Foo'], [
            new DocumentationPage('one/foo', ['navigation.group' => 'Group 1']),
            new DocumentationPage('two/foo', ['navigation.group' => 'Group 2']),
        ]);
    }

    public function testSidebarAutomaticDropdownItemsWithSameLabelButDifferentGroupsAreFiltered()
    {
        $this->assertSidebarEquals(['Foo'], [
            new DocumentationPage('one/foo'),
            new DocumentationPage('two/foo'),
        ]);
    }

    public function testSidebarDropdownItemsWithSameLabelButDifferentGroupsAreNotFilteredWithFlattenedOutputPaths()
    {
        config(['docs.flattened_output_paths' => false]);

        $this->assertSidebarEquals([
            ['label' => 'Foo', 'group' => 'group-1'],
            ['label' => 'Foo', 'group' => 'group-2'],
        ], [
            new DocumentationPage('one/foo', ['navigation.group' => 'Group 1']),
            new DocumentationPage('two/foo', ['navigation.group' => 'Group 2']),
        ]);
    }

    public function testSidebarAutomaticDropdownItemsWithSameLabelButDifferentGroupsAreNotFilteredWithFlattenedOutputPaths()
    {
        config(['docs.flattened_output_paths' => false]);

        $this->assertSidebarEquals([
            // Todo: Should use proper group name
            ['label' => 'Foo', 'group' => 'one'],
            ['label' => 'Foo', 'group' => 'two'],
        ], [
            new DocumentationPage('one/foo'),
            new DocumentationPage('two/foo'),
        ]);
    }

    // Testing helpers

    protected function assertSidebarEquals(array $expected, array $menuPages): void
    {
        $this->sidebar($menuPages)->assertEquals($expected);
    }

    protected function assertMenuEquals(array $expected, array $menuPages): void
    {
        $this->menu($menuPages)->assertEquals($expected);
    }

    #[NoReturn]
    protected function ddMenu(?array $menuPages = null, ?string $menu = 'menu'): void
    {
        if ($menu === 'sidebar') {
            dd($this->sidebar($menuPages)->state());
        }

        dd($this->menu($menuPages)->state());
    }

    protected function menu(?array $withPages = null): AssertableNavigationMenu
    {
        if ($withPages) {
            $this->withPages($withPages);
        }

        return new AssertableNavigationMenu($this);
    }

    protected function withPages(array $pages): static
    {
        $this->kernel->setRoutes(collect($pages)->map(fn (HydePage $page) => $page->getRoute()));

        return $this;
    }

    protected function sidebar(?array $withPages = null): AssertableNavigationMenu
    {
        if ($withPages) {
            $this->withPages($withPages);
        }

        return new AssertableNavigationMenu($this, true);
    }
}

class TestNavItem
{
    public readonly string $label;
    public readonly ?string $group;
    public readonly int $priority;
    public readonly array $children;

    public function __construct(string $label, ?string $group, int $priority, array $children)
    {
        $this->label = $label;
        $this->group = $group;
        $this->priority = $priority;
        $this->children = collect($children)->map(fn (NavItem $child) => $child->getLabel())->toArray();
    }

    public static function properties(): array
    {
        return ['label', 'group', 'priority', 'children'];
    }
}

class AssertableNavigationMenu
{
    protected TestCase $test;
    protected Collection $items;

    public function __construct(TestCase $test, $sidebar = false)
    {
        $this->items = $sidebar
            ? GeneratesDocumentationSidebarMenu::handle()->getItems()
            : GeneratesMainNavigationMenu::handle()->getItems();

        $this->test = $test;
    }

    /** A simplified serialized format for comparisons */
    public function state(): array
    {
        return $this->items->map(function (NavItem $item): TestNavItem {
            return new TestNavItem($item->getLabel(), $item->getGroup(), $item->getPriority(), $item->getChildren());
        })->toArray();
    }

    public function getState(int $index): ?TestNavItem
    {
        return $this->state()[$index] ?? null;
    }

    /**
     * @param  array  $expected  The expected state format
     * @param  bool  $strict  If false, missing array keys are ignored
     */
    public function assertEquals(array $expected, bool $strict = false): static
    {
        foreach ($expected as $index => $item) {
            if (! is_array($item)) {
                $item = ['label' => $item];
            }

            foreach (TestNavItem::properties() as $property) {
                if ($this->getState($index) !== null) {
                    if (isset($item[$property])) {
                        $this->test->assertSame($item[$property], $this->getState($index)->$property, "Failed to match the expected value for '$property'");
                    } elseif ($strict) {
                        $this->test->fail("Missing array key '$property' in the expected state");
                    }
                }
            }
        }

        $this->test->assertCount(count($expected), $this->state(), 'The expected state has a different count than the actual state');

        return $this;
    }
}

class TestKernel extends HydeKernel
{
    protected ?RouteCollection $mockRoutes = null;

    public function setRoutes(Collection $routes): void
    {
        $this->mockRoutes = RouteCollection::make($routes);
    }

    /** @return \Hyde\Foundation\Kernel\RouteCollection<string, \Hyde\Support\Models\Route> */
    public function routes(): RouteCollection
    {
        return $this->mockRoutes ?? parent::routes();
    }
}
