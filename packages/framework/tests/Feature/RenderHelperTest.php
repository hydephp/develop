<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Pages\MarkdownPage;
use Hyde\Support\Facades\Render;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Support\Models\Render
 * @covers \Hyde\Support\Facades\Render
 */
class RenderHelperTest extends TestCase
{
    public function testSetAndGetPage()
    {
        Render::setPage($page = new MarkdownPage());
        $this->assertSame($page, Render::getPage());
    }

    public function testGetCurrentRoute()
    {
        Render::setPage($page = new MarkdownPage());
        $this->assertEquals($page->getRoute(), Render::getCurrentRoute());
    }

    public function testGetCurrentPage()
    {
        Render::setPage($page = new MarkdownPage());
        $this->assertSame($page->getRouteKey(), Render::getCurrentPage());
    }

    public function testShareAndShared()
    {
        Render::share('foo', 'bar');
        $this->assertEquals('bar', Render::shared('foo'));
    }

    public function testSharedWithDefault()
    {
        $this->assertEquals('bar', Render::shared('foo', 'bar'));
    }

    public function testHas()
    {
        Render::share('foo', 'bar');
        $this->assertTrue(Render::has('foo'));
        $this->assertFalse(Render::has('bar'));
    }
}
