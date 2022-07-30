<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\HydeKernel::hasSiteUrl
 * @covers \Hyde\Framework\HydeKernel::qualifiedUrl
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

    // test that qualifiedUrl returns the site url when no path is given
    public function test_qualified_url_returns_site_url_when_no_path_is_given()
    {
        config(['site.url' => 'https://example.com']);
        $this->assertEquals('https://example.com', Hyde::qualifiedUrl());
    }

    // test that qualifiedUrl returns the site url plus the given path
    public function test_qualified_url_returns_site_url_plus_given_path()
    {
        config(['site.url' => 'https://example.com']);
        $this->assertEquals('https://example.com/path', Hyde::qualifiedUrl('path'));
    }

    // test that qualifiedUrl returns the site url plus the given path with extension
    public function test_qualified_url_returns_site_url_plus_given_path_with_extension()
    {
        config(['site.url' => 'https://example.com']);
        $this->assertEquals('https://example.com/path.html', Hyde::qualifiedUrl('path.html'));
    }

    // test that qualifiedUrl returns the site url plus the given path with extension and query string
    public function test_qualified_url_returns_site_url_plus_given_path_with_extension_and_query_string()
    {
        config(['site.url' => 'https://example.com']);
        $this->assertEquals('https://example.com/path.html?query=string', Hyde::qualifiedUrl('path.html?query=string'));
    }

    // test that qualifiedUrl trims trailing slashes
    public function test_qualified_url_trims_trailing_slashes()
    {
        config(['site.url' => 'https://example.com/']);
        $this->assertEquals('https://example.com', Hyde::qualifiedUrl());
        $this->assertEquals('https://example.com', Hyde::qualifiedUrl('/'));
        $this->assertEquals('https://example.com/foo', Hyde::qualifiedUrl('/foo/'));
    }

    // test that qualifiedUrl accepts multiple schemes
    public function test_qualified_url_accepts_multiple_schemes()
    {
        config(['site.url' => 'http://example.com']);
        $this->assertEquals('http://example.com', Hyde::qualifiedUrl());
    }

    // test that qualifiedUrl throws an exception when no site url is set
    public function test_qualified_url_throws_exception_when_no_site_url_is_set()
    {
        config(['site.url' => null]);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No site URL has been set in config (or .env).');
        Hyde::qualifiedUrl();
    }

    public function test_helper_returns_expected_string_when_site_url_is_set()
    {
        config(['site.url' => 'https://example.com']);
        $this->assertEquals('https://example.com/foo/bar.html', Hyde::uriPath('foo/bar.html'));
    }
}
