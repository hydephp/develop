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

    protected function setUp(): void
    {
        parent::setUp();

        View::swap(Mockery::mock(Factory::class)->makePartial());
        $renderData = new RenderData();
        Render::swap($renderData);

        $pages = [
            'foo' => 'one',
            'bar' => 'two',
            'baz' => 'three',
        ];

        foreach ($pages as $page => $group) {
            $page = new DocumentationPage($page, ['navigation.group' => $group]);
            Routes::addRoute($page->getRoute());
        }
    }

    public function testActiveGroupIsNullInitially()
    {
        $menu = NavigationMenuGenerator::handle(DocumentationSidebar::class);
        $this->assertNull($menu->getActiveGroup());
    }

    public function testSetActiveGroup()
    {
        $menu = NavigationMenuGenerator::handle(DocumentationSidebar::class);

        $renderData = Render::getFacadeRoot();
        $renderData->setPage(new DocumentationPage('foo', ['navigation.group' => 'one']));

        $this->assertInstanceOf(NavigationGroup::class, $menu->getActiveGroup());
        $this->assertSame('one', $menu->getActiveGroup()->getGroupKey());
    }

    public function testActiveGroupUpdatesWithPageChanges()
    {
        $menu = NavigationMenuGenerator::handle(DocumentationSidebar::class);
        $renderData = Render::getFacadeRoot();

        $pages = [
            'foo' => 'one',
            'bar' => 'two',
            'baz' => 'three',
        ];

        foreach ($pages as $page => $group) {
            $renderData->setPage(new DocumentationPage($page, ['navigation.group' => $group]));
            $this->assertSame($group, $menu->getActiveGroup()->getGroupKey());
        }
    }

    public function testActiveGroupItems()
    {
        $menu = NavigationMenuGenerator::handle(DocumentationSidebar::class);
        $renderData = Render::getFacadeRoot();
        $renderData->setPage(new DocumentationPage('foo', ['navigation.group' => 'one']));

        $expectedItems = [
            'one' => true,
            'two' => false,
            'three' => false,
        ];

        $actualItems = $menu->getItems()->mapWithKeys(fn (NavigationGroup $item): array => [
            $item->getGroupKey() => $item === $menu->getActiveGroup(),
        ])->all();

        $this->assertSame($expectedItems, $actualItems);
    }
}
