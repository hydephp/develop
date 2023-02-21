<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\TestCase;

class UnixsumTest extends TestCase
{
    public function test_method_returns_string()
    {
        $this->assertIsString(\hyde\unixsum('foo'));
    }

    public function test_method_returns_string_with_length_of_32()
    {
        $this->assertEquals(32, strlen(\hyde\unixsum('foo')));
    }

    public function test_method_returns_string_matching_expected_format()
    {
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', \hyde\unixsum('foo'));
    }

    public function test_method_returns_same_value_for_same_string_using_normal_method()
    {
        $this->assertEquals(md5('foo'), \hyde\unixsum('foo'));
    }

    public function test_method_returns_different_value_for_different_string()
    {
        $this->assertNotEquals(\hyde\unixsum('foo'), \hyde\unixsum('bar'));
    }

    public function test_function_is_case_sensitive()
    {
        $this->assertNotEquals(\hyde\unixsum('foo'), \hyde\unixsum('FOO'));
    }

    public function test_function_is_space_sensitive()
    {
        $this->assertNotEquals(\hyde\unixsum(' foo '), \hyde\unixsum('foo'));
    }

    public function test_method_returns_same_value_regardless_of_end_of_line_sequence()
    {
        $this->assertEquals(\hyde\unixsum('foo'), \hyde\unixsum('foo'));
        $this->assertEquals(\hyde\unixsum("foo\n"), \hyde\unixsum("foo\n"));
        $this->assertEquals(\hyde\unixsum("foo\n"), \hyde\unixsum("foo\r"));
        $this->assertEquals(\hyde\unixsum("foo\n"), \hyde\unixsum("foo\r\n"));
    }

    public function test_method_returns_same_value_for_string_with_mixed_end_of_line_sequences()
    {
        $this->assertEquals(\hyde\unixsum("foo\nbar\r\nbaz\r\n"),
            \hyde\unixsum("foo\nbar\nbaz\n"));
    }

    public function test_method_returns_same_value_when_loaded_from_file()
    {
        $string = "foo\nbar\r\nbaz\r\n";
        $file = tempnam(sys_get_temp_dir(), 'foo');
        file_put_contents($file, $string);

        $this->assertEquals(\hyde\unixsum($string), \hyde\unixsum(file_get_contents($file)));

        unlink($file);
    }

    public function test_method_returns_same_value_when_loaded_from_file_using_shorthand()
    {
        $string = "foo\nbar\r\nbaz\r\n";
        file_put_contents('foo', $string);

        $this->assertEquals(\hyde\unixsum($string), \hyde\unixsum_file('foo'));

        unlink('foo');
    }
}
