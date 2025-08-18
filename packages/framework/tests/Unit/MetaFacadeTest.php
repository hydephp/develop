<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Facades\Meta;
use Hyde\Framework\Features\Metadata\GlobalMetadataBag;
use Hyde\Testing\UnitTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Facades\Meta::class)]
class MetaFacadeTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;
    protected static bool $needsRender = true;

    public function testNameMethodReturnsAValidHtmlMetaString()
    {
        $this->assertSame('<meta name="foo" content="bar">', (string) Meta::name('foo', 'bar'));
    }

    public function testPropertyMethodReturnsAValidHtmlMetaString()
    {
        $this->assertSame('<meta property="og:foo" content="bar">', (string) Meta::property('foo', 'bar'));
    }

    public function testPropertyMethodAcceptsPropertyWithOgPrefix()
    {
        $this->assertSame('<meta property="og:foo" content="bar">', (string) Meta::property('og:foo', 'bar'));
    }

    public function testPropertyMethodAcceptsPropertyWithoutOgPrefix()
    {
        $this->assertSame('<meta property="og:foo" content="bar">', (string) Meta::property('foo', 'bar'));
    }

    public function testLinkMethodReturnsAValidHtmlLinkString()
    {
        $this->assertSame('<link rel="foo" href="bar">', (string) Meta::link('foo', 'bar'));
    }

    public function testLinkMethodReturnsAValidHtmlLinkStringWithAttributes()
    {
        $this->assertSame('<link rel="foo" href="bar" title="baz">', (string) Meta::link('foo', 'bar', ['title' => 'baz']));
    }

    public function testLinkMethodReturnsAValidHtmlLinkStringWithMultipleAttributes()
    {
        $this->assertSame('<link rel="foo" href="bar" title="baz" type="text/css">', (string) Meta::link('foo', 'bar', ['title' => 'baz', 'type' => 'text/css']));
    }

    public function testGetMethodReturnsGlobalMetadataBag()
    {
        $this->assertEquals(Meta::get(), GlobalMetadataBag::make());
    }

    public function testRenderMethodRendersGlobalMetadataBag()
    {
        $this->assertSame(Meta::render(), GlobalMetadataBag::make()->render());
    }
}
