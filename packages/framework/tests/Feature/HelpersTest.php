<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

class HelpersTest extends TestCase
{
    /** @covers ::hyde */
    public function test_hyde_function_exists()
    {
        $this->assertTrue(function_exists('hyde'));
    }

    /** @covers ::hyde */
    public function test_hyde_function_returns_hyde_class()
    {
        $this->assertInstanceOf(Hyde::class, hyde());
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

    /** @covers ::array_map_unique */
    public function test_array_map_unique_function_exists()
    {
        $this->assertTrue(function_exists('array_map_unique'));
    }

    /** @covers ::array_map_unique */
    public function test_array_map_unique_function_accepts_array_or_collection()
    {
        $array = [1, 2, 3];
        $collection = collect($array);

        $this->assertSame($array, array_map_unique($array, function ($item) {
            return $item;
        }));
        $this->assertSame($array, array_map_unique($collection, function ($item) {
            return $item;
        }));
    }

    /** @covers ::array_map_unique */
    public function test_array_map_unique_function_returns_unique_array()
    {
        $array = [1, 1, 2];

        $this->assertEquals([1, 2], array_map_unique($array, function ($item) {
            return $item;
        }));
    }

    /** @covers ::array_map_unique */
    public function test_array_map_unique_function_returns_reset_keys()
    {
        $array = [1, 2, 2, 2, 3];

        $this->assertEquals([1, 2, 3], array_map_unique($array, function ($item) {
            return $item;
        }));
    }

    public function test_array_map_unique_function_handles_string_arrays()
    {
        $array = ['foo', 'foo', 'bar'];

        $this->assertEquals(['foo', 'bar'], array_map_unique($array, function ($item) {
            return $item;
        }));
    }
}
