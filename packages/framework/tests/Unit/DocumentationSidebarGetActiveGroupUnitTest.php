<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Mockery;
use Illuminate\View\Factory;
use Hyde\Testing\UnitTestCase;
use Hyde\Support\Facades\Render;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Models\RenderData;
use Illuminate\Support\Facades\View;
use Hyde\Framework\Features\Navigation\NavigationItem;
use Hyde\Framework\Features\Navigation\NavigationGroup;
use Hyde\Framework\Features\Navigation\DocumentationSidebar;

/**
 * @covers \Hyde\Framework\Features\Navigation\DocumentationSidebar
 *
 * @see \Hyde\Framework\Testing\Feature\Services\DocumentationSidebarTest
 * @see \Hyde\Framework\Testing\Unit\DocumentationSidebarGetActiveGroupUnitTest
 */
class DocumentationSidebarGetActiveGroupUnitTest extends UnitTestCase
{
    protected static bool $needsKernel = true;

    protected RenderData $renderData;

    protected function setUp(): void
    {
        parent::setUp();

        View::swap(Mockery::mock(Factory::class)->makePartial());
        $this->renderData = new RenderData();
        Render::swap($this->renderData);

        self::mockConfig();
    }

    public function testGetActiveGroup()
    {
        $sidebar = $this->createSidebar();

        // Assert is null when there is no active group
        $this->assertNull($sidebar->getActiveGroup());

        // Assert is null when active group is outside the sidebar
        $this->mockCurrentPageForActiveGroup('foo');
        $this->assertNull($sidebar->getActiveGroup());

        // Assert is null when sidebar is not collapsible
        self::mockConfig(['docs.sidebar.collapsible' => false]);
        $this->mockCurrentPageForActiveGroup('getting-started');
        $this->assertNull($sidebar->getActiveGroup());
        self::mockConfig(['docs.sidebar.collapsible' => true]);

        // Assert the active group
        $this->mockCurrentPageForActiveGroup('getting-started');
        $this->assertSame('getting-started', $sidebar->getActiveGroup()->getGroupKey());

        // Assert the active group
        $this->mockCurrentPageForActiveGroup('configuration');
        $this->assertSame('configuration', $sidebar->getActiveGroup()->getGroupKey());

        // Assert the active group
        $this->mockCurrentPageForActiveGroup('usage');
        $this->assertSame('usage', $sidebar->getActiveGroup()->getGroupKey());

        // Assert the active group for identifier within the group
        $this->mockCurrentPageForActiveGroup('getting-started', 'Introduction');
        $this->assertSame('getting-started', $sidebar->getActiveGroup()->getGroupKey());

        // Assert the active group for identifier within the group
        $this->mockCurrentPageForActiveGroup('configuration', 'Configuration');
        $this->assertSame('configuration', $sidebar->getActiveGroup()->getGroupKey());

        // Assert the active group for identifier within the group
        $this->mockCurrentPageForActiveGroup('usage', 'Routing');
        $this->assertSame('usage', $sidebar->getActiveGroup()->getGroupKey());

        // Assert the active group for with group key having different casing
        $this->mockCurrentPageForActiveGroup('GETTING-STARTED');
        $this->assertSame('getting-started', $sidebar->getActiveGroup()->getGroupKey());

        // Assert the active group for with group key having different casing
        $this->mockCurrentPageForActiveGroup('Getting-Started');
        $this->assertSame('getting-started', $sidebar->getActiveGroup()->getGroupKey());

        // Assert the active group for with group key having different casing
        $this->mockCurrentPageForActiveGroup('getting-started');
        $this->assertSame('getting-started', $sidebar->getActiveGroup()->getGroupKey());
    }

    public function testGetActiveGroupIsNullWhenNoItemsExist()
    {
        $this->assertNull((new DocumentationSidebar())->getActiveGroup());
    }

    public function testGetActiveGroupIsNullWhenNoGroupsExist()
    {
        $this->assertNull((new DocumentationSidebar([new NavigationItem('foo', 'Foo')]))->getActiveGroup());
    }

    protected function mockCurrentPageForActiveGroup(string $group, string $identifier = 'foo'): void
    {
        $this->renderData->setPage(new DocumentationPage($identifier, ['navigation.group' => $group]));
    }

    protected function createSidebar(): DocumentationSidebar
    {
        // The sidebar structure
        $items = [
            // Group keys
            'getting-started' => [
                // Group items
                'Introduction',
                'Installation',
            ],
            'configuration' => [
                'Configuration',
                'Environment Variables',
            ],
            'usage' => [
                'Routing',
                'Middleware',
            ],
        ];

        // Create the sidebar items
        foreach ($items as $groupKey => $groupItems) {
            $items[$groupKey] = new NavigationGroup($groupKey, array_map(fn (string $item): NavigationItem => new NavigationItem($item, $item), $groupItems));
        }

        // Create the sidebar
        return new DocumentationSidebar($items);
    }
}
