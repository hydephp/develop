<?php

/** @noinspection PhpFullyQualifiedNameUsageInspection */

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Foundation\HydeKernel;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

class HelpersTest extends TestCase
{
    /** @covers ::hyde */
    public function test_hyde_function_exists()
    {
        $this->assertTrue(function_exists('hyde'));
    }

    /** @covers ::hyde */
    public function test_hyde_function_returns_hyde_kernel_class()
    {
        $this->assertInstanceOf(HydeKernel::class, hyde());
    }

    /** @covers ::hyde */
    public function test_can_call_methods_on_returned_hyde_class()
    {
        $this->assertSame(Hyde::path(), hyde()->path());
    }

    /** @covers ::unslash */
    public function test_unslash_function_exists()
    {
        $this->assertTrue(function_exists('unslash'));
    }

    /** @covers ::unslash */
    public function test_unslash_function_trims_trailing_slashes()
    {
        $tests = ['foo',  '/foo',  'foo/',  '/foo/',  '\foo\\',  '\\/foo/\\'];

        foreach ($tests as $test) {
            $this->assertSame('foo', unslash($test));
        }

        $tests = ['',  '/',  '\\',  '/\\'];

        foreach ($tests as $test) {
            $this->assertSame('', unslash($test));
        }

        $tests = ['foo/bar',  'foo/bar/',  'foo/bar\\',  '\\/foo/bar/\\'];

        foreach ($tests as $test) {
            $this->assertSame('foo/bar', unslash($test));
        }
    }

    /** @covers ::\Hyde\hyde */
    public function test_hyde_function_exists_in_hyde_namespace()
    {
        $this->assertTrue(function_exists('Hyde\hyde'));
    }

    /** @covers ::\Hyde\hyde */
    public function test_namespaced_hyde_function()
    {
        $this->assertSame(hyde(), \Hyde\hyde());
    }

    /** @covers ::\Hyde\unslash */
    public function test_unslash_function_exists_in_hyde_namespace()
    {
        $this->assertTrue(function_exists('Hyde\unslash'));
    }

    /** @covers ::\Hyde\unslash */
    public function test_namespaced_unslash_function()
    {
        $this->assertSame(unslash('foo'), \Hyde\unslash('foo'));
    }

    /** @covers ::\Hyde\make_title */
    public function test_hyde_make_title_function()
    {
        $this->assertSame(Hyde::makeTitle('foo'), \Hyde\make_title('foo'));
    }

    /** @covers ::\Hyde\normalize_newlines */
    public function test_hyde_normalize_newlines_function()
    {
        $this->assertSame(Hyde::normalizeNewlines('foo'), \Hyde\normalize_newlines('foo'));
    }

    /** @covers ::\Hyde\strip_newlines */
    public function test_hyde_strip_newlines_function()
    {
        $this->assertSame(Hyde::stripNewlines('foo'), \Hyde\strip_newlines('foo'));
    }

    /** @covers ::\Hyde\trim_slashes */
    public function test_hyde_trim_slashes_function()
    {
        $this->assertSame(Hyde::trimSlashes('foo'), \Hyde\trim_slashes('foo'));
    }

    /** @covers ::\Hyde\markdown */
    public function test_hyde_markdown_function()
    {
        $this->assertEquals(Hyde::markdown('foo'), \Hyde\markdown('foo'));
    }
}
