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

    public function test_has_site_url_returns_false_when_no_site_url_is_set()
    {
        config(['hyde.url' => null]);
        $this->assertFalse($this->class->hasSiteUrl());
    }

    public function test_has_site_url_returns_true_when_site_url_is_set()
    {
        config(['hyde.url' => 'https://example.com']);
        $this->assertTrue($this->class->hasSiteUrl());
    }

    public function test_qualified_url_returns_site_url_when_no_path_is_given()
    {
        config(['hyde.url' => 'https://example.com']);
        $this->assertEquals('https://example.com', $this->class->url());
    }

    public function test_qualified_url_returns_site_url_plus_given_path()
    {
        config(['hyde.url' => 'https://example.com']);
        $this->assertEquals('https://example.com/path', $this->class->url('path'));
    }

    public function test_qualified_url_returns_site_url_plus_given_path_with_extension()
    {
        config(['hyde.url' => 'https://example.com']);
        $this->assertEquals('https://example.com/path.html', $this->class->url('path.html'));
    }

    public function test_qualified_url_returns_site_url_plus_given_path_with_extension_and_query_string()
    {
        config(['hyde.url' => 'https://example.com']);
        $this->assertEquals('https://example.com/path.html?query=string', $this->class->url('path.html?query=string'));
    }

    public function test_qualified_url_trims_trailing_slashes()
    {
        config(['hyde.url' => 'https://example.com/']);
        $this->assertEquals('https://example.com', $this->class->url());
        $this->assertEquals('https://example.com', $this->class->url('/'));
        $this->assertEquals('https://example.com/foo', $this->class->url('/foo/'));
    }

    public function test_qualified_url_accepts_multiple_schemes()
    {
        config(['hyde.url' => 'http://example.com']);
        $this->assertEquals('http://example.com', $this->class->url());
    }

    public function test_qualified_url_throws_exception_when_no_site_url_is_set()
    {
        config(['hyde.url' => null]);
        $this->expectException(BaseUrlNotSetException::class);
        $this->expectExceptionMessage('No site URL has been set in config (or .env).');
        $this->class->url();
    }

    public function test_helper_returns_expected_string_when_site_url_is_set()
    {
        config(['hyde.url' => 'https://example.com']);
        $this->assertEquals('https://example.com/foo/bar.html', $this->class->url('foo/bar.html'));
    }

    public function test_helper_returns_expected_string_when_pretty_urls_are_enabled()
    {
        config(['hyde.url' => 'https://example.com', 'hyde.pretty_urls' => true]);
        $this->assertEquals('https://example.com', $this->class->url('index.html'));
        $this->assertEquals('https://example.com/foo', $this->class->url('foo.html'));
        $this->assertEquals('https://example.com/docs', $this->class->url('docs/index.html'));
    }
}
