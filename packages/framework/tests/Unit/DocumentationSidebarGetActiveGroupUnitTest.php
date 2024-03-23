<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Mockery;
use Illuminate\View\Factory;
use Hyde\Testing\UnitTestCase;
use Hyde\Support\Facades\Render;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Models\RenderData;
use Hyde\Foundation\Facades\Routes;
use Illuminate\Support\Facades\View;
use Hyde\Framework\Features\Navigation\NavigationGroup;
use Hyde\Framework\Features\Navigation\DocumentationSidebar;
use Hyde\Framework\Features\Navigation\NavigationMenuGenerator;

/**
 * @covers \Hyde\Framework\Features\Navigation\DocumentationSidebar
 *
 * @see \Hyde\Framework\Testing\Feature\Services\DocumentationSidebarTest
 * @see \Hyde\Framework\Testing\Unit\DocumentationSidebarGetActiveGroupUnitTest
 */
class DocumentationSidebarGetActiveGroupUnitTest extends UnitTestCase
{
    protected static bool $needsConfig = true;
    protected static bool $needsKernel = true;

    protected RenderData $renderData;
    protected DocumentationSidebar $menu;

    protected function setUp(): void
    {
        parent::setUp();

        View::swap(Mockery::mock(Factory::class)->makePartial());
        $this->renderData = new RenderData();
        Render::swap($this->renderData);

        $pages = [
            'foo' => 'one',
            'bar' => 'two',
            'baz' => 'three',
        ];

        foreach ($pages as $page => $group) {
            $page = new DocumentationPage($page, ['navigation.group' => $group]);
            Routes::addRoute($page->getRoute());
        }

        $this->menu = NavigationMenuGenerator::handle(DocumentationSidebar::class);
    }

    public function testActiveGroupIsNullInitially()
    {
        $menu = new DocumentationSidebar();
        $this->assertNull($menu->getActiveGroup());
    }

    public function testActiveGroupIsNullWhenNoGroupIsActive()
    {
        $this->assertNull($this->menu->getActiveGroup());
    }

    public function testSetActiveGroup()
    {
        $this->renderData->setPage(new DocumentationPage('foo', ['navigation.group' => 'one']));

        $this->assertInstanceOf(NavigationGroup::class, $this->menu->getActiveGroup());
        $this->assertSame('one', $this->menu->getActiveGroup()->getGroupKey());
    }

    public function testActiveGroupUpdatesWithPageChanges()
    {
        $pages = [
            'foo' => 'one',
            'bar' => 'two',
            'baz' => 'three',
        ];

        foreach ($pages as $page => $group) {
            $this->renderData->setPage(new DocumentationPage($page, ['navigation.group' => $group]));
            $this->assertSame($group, $this->menu->getActiveGroup()->getGroupKey());
        }
    }

    public function testActiveGroupItems()
    {
        $this->renderData->setPage(new DocumentationPage('foo', ['navigation.group' => 'one']));

        $expectedItems = [
            'one' => true,
            'two' => false,
            'three' => false,
        ];

        $actualItems = $this->menu->getItems()->mapWithKeys(fn (NavigationGroup $item): array => [
            $item->getGroupKey() => $item === $this->menu->getActiveGroup(),
        ])->all();

        $this->assertSame($expectedItems, $actualItems);
    }
}
