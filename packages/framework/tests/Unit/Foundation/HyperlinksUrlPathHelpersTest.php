<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Foundation;

use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Kernel\Hyperlinks;
use Hyde\Framework\Exceptions\BaseUrlNotSetException;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Foundation\Kernel\Hyperlinks
 * @covers \Hyde\Framework\Exceptions\BaseUrlNotSetException
 */
class HyperlinksUrlPathHelpersTest extends TestCase
{
    protected Hyperlinks $class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->class = new Hyperlinks(HydeKernel::getInstance());
    }

    public function testHasSiteUrlReturnsFalseWhenNoSiteUrlIsSet()
    {
        config(['hyde.url' => null]);
        $this->assertFalse($this->class->hasSiteUrl());
    }

    public function testHasSiteUrlReturnsTrueWhenSiteUrlIsSet()
    {
        config(['hyde.url' => 'https://example.com']);
        $this->assertTrue($this->class->hasSiteUrl());
    }

    public function testQualifiedUrlReturnsSiteUrlWhenNoPathIsGiven()
    {
        config(['hyde.url' => 'https://example.com']);
        $this->assertEquals('https://example.com', $this->class->url());
    }

    public function testQualifiedUrlReturnsSiteUrlPlusGivenPath()
    {
        config(['hyde.url' => 'https://example.com']);
        $this->assertEquals('https://example.com/path', $this->class->url('path'));
    }

    public function testQualifiedUrlReturnsSiteUrlPlusGivenPathWithExtension()
    {
        config(['hyde.url' => 'https://example.com']);
        $this->assertEquals('https://example.com/path.html', $this->class->url('path.html'));
    }

    public function testQualifiedUrlReturnsSiteUrlPlusGivenPathWithExtensionAndQueryString()
    {
        config(['hyde.url' => 'https://example.com']);
        $this->assertEquals('https://example.com/path.html?query=string', $this->class->url('path.html?query=string'));
    }

    public function testQualifiedUrlTrimsTrailingSlashes()
    {
        config(['hyde.url' => 'https://example.com/']);
        $this->assertEquals('https://example.com', $this->class->url());
        $this->assertEquals('https://example.com', $this->class->url('/'));
        $this->assertEquals('https://example.com/foo', $this->class->url('/foo/'));
    }

    public function testQualifiedUrlAcceptsMultipleSchemes()
    {
        config(['hyde.url' => 'http://example.com']);
        $this->assertEquals('http://example.com', $this->class->url());
    }

    public function testQualifiedUrlThrowsExceptionWhenNoSiteUrlIsSet()
    {
        config(['hyde.url' => null]);
        $this->expectException(BaseUrlNotSetException::class);
        $this->expectExceptionMessage('No site URL has been set in config (or .env).');
        $this->class->url();
    }

    public function testHelperReturnsExpectedStringWhenSiteUrlIsSet()
    {
        config(['hyde.url' => 'https://example.com']);
        $this->assertEquals('https://example.com/foo/bar.html', $this->class->url('foo/bar.html'));
    }

    public function testHelperReturnsExpectedStringWhenPrettyUrlsAreEnabled()
    {
        config(['hyde.url' => 'https://example.com', 'hyde.pretty_urls' => true]);
        $this->assertEquals('https://example.com', $this->class->url('index.html'));
        $this->assertEquals('https://example.com/foo', $this->class->url('foo.html'));
        $this->assertEquals('https://example.com/docs', $this->class->url('docs/index.html'));
    }
}
