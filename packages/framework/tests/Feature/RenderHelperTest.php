<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Pages\MarkdownPage;
use Hyde\Support\Facades\Render;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\View;

/**
 * @covers \Hyde\Support\Models\Render
 * @covers \Hyde\Support\Facades\Render
 */
class RenderHelperTest extends TestCase
{
    public function testSetAndGetPage()
    {
        $this->assertNull(Render::getPage());

        Render::setPage($page = new MarkdownPage());
        $this->assertSame($page, Render::getPage());
    }

    public function testGetCurrentRoute()
    {
        $this->assertNull(Render::getCurrentRoute());

        Render::setPage($page = new MarkdownPage());
        $this->assertEquals($page->getRoute(), Render::getCurrentRoute());
    }

    public function testGetCurrentPage()
    {
        $this->assertNull(Render::getCurrentPage());

        Render::setPage($page = new MarkdownPage());
        $this->assertSame($page->getRouteKey(), Render::getCurrentPage());
    }

    public function testShareToView()
    {
        $this->assertNull(View::shared('page'));
        $this->assertNull(View::shared('currentRoute'));
        $this->assertNull(View::shared('currentPage'));

        Render::setPage($page = new MarkdownPage());
        Render::shareToView();

        $this->assertSame($page, View::shared('page'));
        $this->assertEquals($page->getRoute(), View::shared('currentRoute'));
        $this->assertSame($page->getRouteKey(), View::shared('currentPage'));
    }

    public function testShare()
    {
        $this->assertNull(Render::getCurrentPage());

        Render::share('currentPage', 'bar');
        $this->assertSame('bar', Render::getCurrentPage());
    }

    public function testShareInvalidProperty()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Property 'foo' does not exist on Hyde\Support\Models\Render");

        Render::share('foo', 'bar');
    }

    public function testShareCascadesDataToView()
    {
        $this->assertNull(View::shared('currentPage'));

        Render::share('currentPage', 'bar');
        $this->assertSame('bar', View::shared('currentPage'));
    }

    public function testClearData()
    {
        Render::setPage(new MarkdownPage());
        $this->assertNotNull(Render::getPage());

        Render::clearData();
        $this->assertNull(Render::getPage());
    }

    public function testClearDataCascadesToClearItsViewData()
    {
        Render::setPage(new MarkdownPage());
        Render::shareToView();

        $this->assertNotNull(View::shared('page'));
        $this->assertNotNull(View::shared('currentRoute'));
        $this->assertNotNull(View::shared('currentPage'));

        Render::clearData();

        $this->assertNull(View::shared('page'));
        $this->assertNull(View::shared('currentRoute'));
        $this->assertNull(View::shared('currentPage'));

        View::share('keep', 'this');
        $this->assertNotNull(View::shared('keep'));

        Render::clearData();
        $this->assertNotNull(View::shared('keep'));
    }

    public function testToArray()
    {
        $this->assertSame([
            'page' => null,
            'currentRoute' => null,
            'currentPage' => null,
        ], Render::toArray());

        Render::setPage($page = new MarkdownPage());
        $this->assertEquals([
            'page' => $page,
            'currentRoute' => $page->getRoute(),
            'currentPage' => $page->getRouteKey(),
        ], Render::toArray());
    }
}
