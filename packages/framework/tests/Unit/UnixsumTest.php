<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Foundation\HydeKernel;
use Illuminate\Filesystem\Filesystem;
use Mockery;
use PHPUnit\Framework\TestCase;

use function Hyde\unixsum;
use function Hyde\unixsum_file;

class UnixsumTest extends TestCase
{
    public function test_method_returns_string()
    {
        $this->assertIsString(unixsum('foo'));
    }

    public function test_method_returns_string_with_length_of_32()
    {
        $this->assertEquals(32, strlen(unixsum('foo')));
    }

    public function test_method_returns_string_matching_expected_format()
    {
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', unixsum('foo'));
    }

    public function test_method_returns_same_value_for_same_string_using_normal_method()
    {
        $this->assertEquals(md5('foo'), unixsum('foo'));
    }

    public function test_method_returns_different_value_for_different_string()
    {
        $this->assertNotEquals(unixsum('foo'), unixsum('bar'));
    }

    public function test_function_is_case_sensitive()
    {
        $this->assertNotEquals(unixsum('foo'), unixsum('FOO'));
    }

    public function test_function_is_space_sensitive()
    {
        $this->assertNotEquals(unixsum(' foo '), unixsum('foo'));
    }

    public function test_method_returns_same_value_regardless_of_end_of_line_sequence()
    {
        $this->assertEquals(unixsum('foo'), unixsum('foo'));
        $this->assertEquals(unixsum("foo\n"), unixsum("foo\n"));
        $this->assertEquals(unixsum("foo\n"), unixsum("foo\r"));
        $this->assertEquals(unixsum("foo\n"), unixsum("foo\r\n"));
    }

    public function test_method_returns_same_value_for_string_with_mixed_end_of_line_sequences()
    {
        $this->assertEquals(unixsum("foo\nbar\r\nbaz\r\n"),
            unixsum("foo\nbar\nbaz\n"));
    }

    public function test_method_returns_same_value_when_loaded_from_file_using_shorthand()
    {
        $string = "foo\nbar\r\nbaz\r\n";

        HydeKernel::setInstance(new HydeKernel());
        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('get')->andReturn($string);
        app()->instance(Filesystem::class, $filesystem);

        $this->assertEquals(unixsum($string), unixsum_file('foo'));
    }
}
