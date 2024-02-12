<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Meta;
use Hyde\Framework\Features\Metadata\GlobalMetadataBag;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Facades\Meta
 */
class MetadataHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['hyde.meta' => []]);
        config(['hyde.url' => null]);
    }

    public function testNameMethodReturnsAValidHtmlMetaString()
    {
        $this->assertEquals(
            '<meta name="foo" content="bar">',
            Meta::name('foo', 'bar')
        );
    }

    public function testPropertyMethodReturnsAValidHtmlMetaString()
    {
        $this->assertEquals(
            '<meta property="og:foo" content="bar">',
            Meta::property('foo', 'bar')
        );
    }

    public function testPropertyMethodAcceptsPropertyWithOgPrefix()
    {
        $this->assertEquals(
            '<meta property="og:foo" content="bar">',
            Meta::property('og:foo', 'bar')
        );
    }

    public function testPropertyMethodAcceptsPropertyWithoutOgPrefix()
    {
        $this->assertEquals(
            '<meta property="og:foo" content="bar">',
            Meta::property('foo', 'bar')
        );
    }

    public function testLinkMethodReturnsAValidHtmlLinkString()
    {
        $this->assertEquals(
            '<link rel="foo" href="bar">',
            Meta::link('foo', 'bar')
        );
    }

    public function testLinkMethodReturnsAValidHtmlLinkStringWithAttributes()
    {
        $this->assertEquals(
            '<link rel="foo" href="bar" title="baz">',
            Meta::link('foo', 'bar', ['title' => 'baz'])
        );
    }

    public function testLinkMethodReturnsAValidHtmlLinkStringWithMultipleAttributes()
    {
        $this->assertEquals(
            '<link rel="foo" href="bar" title="baz" type="text/css">',
            Meta::link('foo', 'bar', ['title' => 'baz', 'type' => 'text/css'])
        );
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
