<?php

namespace Hyde\Framework\Testing\Unit\Foundation;

use Hyde\Framework\Exceptions\BaseUrlNotSetException;
use Hyde\Framework\Foundation\Hyperlinks;
use Hyde\Framework\HydeKernel;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Foundation\Hyperlinks::hasSiteUrl
 * @covers \Hyde\Framework\Foundation\Hyperlinks::url
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
        config(['site.url' => null]);
        $this->assertFalse($this->class->hasSiteUrl());
    }

    public function test_has_site_url_returns_true_when_site_url_is_set()
    {
        config(['site.url' => 'https://example.com']);
        $this->assertTrue($this->class->hasSiteUrl());
    }

    // test that url returns the site url when no path is given
    public function test_qualified_url_returns_site_url_when_no_path_is_given()
    {
        config(['site.url' => 'https://example.com']);
        $this->assertEquals('https://example.com', $this->class->url());
    }

    // test that url returns the site url plus the given path
    public function test_qualified_url_returns_site_url_plus_given_path()
    {
        config(['site.url' => 'https://example.com']);
        $this->assertEquals('https://example.com/path', $this->class->url('path'));
    }

    // test that url returns the site url plus the given path with extension
    public function test_qualified_url_returns_site_url_plus_given_path_with_extension()
    {
        config(['site.url' => 'https://example.com']);
        $this->assertEquals('https://example.com/path.html', $this->class->url('path.html'));
    }

    // test that url returns the site url plus the given path with extension and query string
    public function test_qualified_url_returns_site_url_plus_given_path_with_extension_and_query_string()
    {
        config(['site.url' => 'https://example.com']);
        $this->assertEquals('https://example.com/path.html?query=string', $this->class->url('path.html?query=string'));
    }

    // test that url trims trailing slashes
    public function test_qualified_url_trims_trailing_slashes()
    {
        config(['site.url' => 'https://example.com/']);
        $this->assertEquals('https://example.com', $this->class->url());
        $this->assertEquals('https://example.com', $this->class->url('/'));
        $this->assertEquals('https://example.com/foo', $this->class->url('/foo/'));
    }

    // test that url accepts multiple schemes
    public function test_qualified_url_accepts_multiple_schemes()
    {
        config(['site.url' => 'http://example.com']);
        $this->assertEquals('http://example.com', $this->class->url());
    }

    // test that url throws an exception when no site url is set
    public function test_qualified_url_throws_exception_when_no_site_url_is_set()
    {
        config(['site.url' => null]);
        $this->expectException(BaseUrlNotSetException::class);
        $this->expectExceptionMessage('No site URL has been set in config (or .env).');
        $this->class->url();
    }

    // test that url uses default parameter when supplied and no site url is set
    public function test_qualified_url_uses_default_parameter_when_no_site_url_is_set()
    {
        config(['site.url' => null]);
        $this->assertEquals('bar/foo', $this->class->url('foo', 'bar'));
    }

    // test that url does not use default parameter when supplied and a site url is set
    public function test_qualified_url_does_not_use_default_parameter_when_site_url_is_set()
    {
        config(['site.url' => 'https://example.com']);
        $this->assertEquals('https://example.com/foo', $this->class->url('foo', 'bar'));
    }

    public function test_helper_returns_expected_string_when_site_url_is_set()
    {
        config(['site.url' => 'https://example.com']);
        $this->assertEquals('https://example.com/foo/bar.html', $this->class->url('foo/bar.html'));
    }

    // test returned url uses pretty urls when enabled
    public function test_helper_returns_expected_string_when_pretty_urls_are_enabled()
    {
        config(['site.url' => 'https://example.com', 'site.pretty_urls' => true]);
        $this->assertEquals('https://example.com', $this->class->url('index.html'));
        $this->assertEquals('https://example.com/foo', $this->class->url('foo.html'));
        $this->assertEquals('https://example.com/docs', $this->class->url('docs/index.html'));
    }
}
