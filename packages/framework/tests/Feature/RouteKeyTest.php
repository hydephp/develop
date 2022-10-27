<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Routing\RouteKey;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Routing\RouteKey
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
        $this->assertEquals(new RouteKey('foo'), RouteKey::fromPage(BladePage::class, 'foo'));
        $this->assertEquals(new RouteKey('foo'), RouteKey::fromPage(MarkdownPage::class, 'foo'));
        $this->assertEquals(new RouteKey('posts/foo'), RouteKey::fromPage(MarkdownPost::class, 'foo'));
        $this->assertEquals(new RouteKey('docs/foo'), RouteKey::fromPage(DocumentationPage::class, 'foo'));
    }

    public function testFromPageWithNestedIdentifier()
    {
        $this->assertEquals(new RouteKey('foo/bar'), RouteKey::fromPage(BladePage::class, 'foo/bar'));
        $this->assertEquals(new RouteKey('foo/bar'), RouteKey::fromPage(MarkdownPage::class, 'foo/bar'));
        $this->assertEquals(new RouteKey('posts/foo/bar'), RouteKey::fromPage(MarkdownPost::class, 'foo/bar'));
        $this->assertEquals(new RouteKey('docs/foo/bar'), RouteKey::fromPage(DocumentationPage::class, 'foo/bar'));
    }
}
