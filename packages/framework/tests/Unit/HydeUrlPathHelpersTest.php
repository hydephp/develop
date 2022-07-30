<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\HydeKernel::hasSiteUrl
 * @covers \Hyde\Framework\HydeKernel::url
 */
class HydeUrlPathHelpersTest extends TestCase
{
    public function test_has_site_url_returns_false_when_no_site_url_is_set()
    {
        config(['site.url' => null]);
        $this->assertFalse(Hyde::hasSiteUrl());
    }

    public function test_has_site_url_returns_true_when_site_url_is_set()
    {
        config(['site.url' => 'https://example.com']);
        $this->assertTrue(Hyde::hasSiteUrl());
    }

    // test that url returns the site url when no path is given
    public function test_qualified_url_returns_site_url_when_no_path_is_given()
    {
        config(['site.url' => 'https://example.com']);
        $this->assertEquals('https://example.com', Hyde::url());
    }

    // test that url returns the site url plus the given path
    public function test_qualified_url_returns_site_url_plus_given_path()
    {
        config(['site.url' => 'https://example.com']);
        $this->assertEquals('https://example.com/path', Hyde::url('path'));
    }

    // test that url returns the site url plus the given path with extension
    public function test_qualified_url_returns_site_url_plus_given_path_with_extension()
    {
        config(['site.url' => 'https://example.com']);
        $this->assertEquals('https://example.com/path.html', Hyde::url('path.html'));
    }

    // test that url returns the site url plus the given path with extension and query string
    public function test_qualified_url_returns_site_url_plus_given_path_with_extension_and_query_string()
    {
        config(['site.url' => 'https://example.com']);
        $this->assertEquals('https://example.com/path.html?query=string', Hyde::url('path.html?query=string'));
    }

    // test that url trims trailing slashes
    public function test_qualified_url_trims_trailing_slashes()
    {
        config(['site.url' => 'https://example.com/']);
        $this->assertEquals('https://example.com', Hyde::url());
        $this->assertEquals('https://example.com', Hyde::url('/'));
        $this->assertEquals('https://example.com/foo', Hyde::url('/foo/'));
    }

    // test that url accepts multiple schemes
    public function test_qualified_url_accepts_multiple_schemes()
    {
        config(['site.url' => 'http://example.com']);
        $this->assertEquals('http://example.com', Hyde::url());
    }

    // test that url throws an exception when no site url is set
    public function test_qualified_url_throws_exception_when_no_site_url_is_set()
    {
        config(['site.url' => null]);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No site URL has been set in config (or .env).');
        Hyde::url();
    }

    // test that url uses default parameter when supplied and no site url is set
    public function test_qualified_url_uses_default_parameter_when_no_site_url_is_set()
    {
        config(['site.url' => null]);
        $this->assertEquals('bar/foo', Hyde::url('foo', 'bar'));
    }

    // test that url does not use default parameter when supplied and a site url is set
    public function test_qualified_url_does_not_use_default_parameter_when_site_url_is_set()
    {
        config(['site.url' => 'https://example.com']);
        $this->assertEquals('https://example.com/foo', Hyde::url('foo', 'bar'));
    }

    public function test_helper_returns_expected_string_when_site_url_is_set()
    {
        config(['site.url' => 'https://example.com']);
        $this->assertEquals('https://example.com/foo/bar.html', Hyde::url('foo/bar.html'));
    }
}
