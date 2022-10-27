<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Models\RouteKey;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Support\Models\RouteKey
 */
class RouteKeyTest extends TestCase
{
    public function testMake()
    {
        $this->assertEquals(RouteKey::make('foo'), new RouteKey('foo'));
    }

    public function test__construct()
    {
        $this->assertInstanceOf(RouteKey::class, new RouteKey('test'));
    }

    public function test__toString()
    {
        $this->assertSame('foo', (string) new RouteKey('foo'));
    }

    public function testGet()
    {
        $this->assertSame('foo', (new RouteKey('foo'))->get());
    }

    public function testFromPage()
    {
        $this->assertSame('foo', RouteKey::fromPage(BladePage::class, 'foo'));
        $this->assertSame('foo', RouteKey::fromPage(MarkdownPage::class, 'foo'));
        $this->assertSame('posts/foo', RouteKey::fromPage(MarkdownPost::class, 'foo'));
        $this->assertSame('docs/foo', RouteKey::fromPage(DocumentationPage::class, 'foo'));
    }
}
