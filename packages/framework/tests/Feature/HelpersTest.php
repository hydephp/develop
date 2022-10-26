<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Hyde;
use Hyde\Framework\HydeKernel;
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
}
