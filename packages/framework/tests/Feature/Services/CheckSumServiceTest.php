<?php

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Framework\Hyde;
use Hyde\Framework\Services\CheckSumService;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Services\CheckSumService
 */
class CheckSumServiceTest extends TestCase
{
    public function test_get_filecache()
    {
        $fileCacheService = new CheckSumService();
        $fileCache = $fileCacheService->getFilecache();

        $this->assertIsArray($fileCache);
        $this->assertArrayHasKey('/resources/views/layouts/app.blade.php', $fileCache);
        $this->assertArrayHasKey('unixsum', $fileCache['/resources/views/layouts/app.blade.php']);
        $this->assertEquals(32, strlen($fileCache['/resources/views/layouts/app.blade.php']['unixsum']));
    }

    public function test_get_checksums()
    {
        $fileCacheService = new CheckSumService();
        $checksums = $fileCacheService->getChecksums();

        $this->assertIsArray($checksums);
        $this->assertEquals(32, strlen($checksums[0]));
    }

    public function test_checksum_matches_any()
    {
        $fileCacheService = new CheckSumService();

        $this->assertTrue($fileCacheService->checksumMatchesAny(CheckSumService::unixsumFile(
            Hyde::vendorPath('resources/views/layouts/app.blade.php'))
        ));
    }

    public function test_checksum_matches_any_false()
    {
        $fileCacheService = new CheckSumService();

        $this->assertFalse($fileCacheService->checksumMatchesAny(CheckSumService::unixsum(
            'foo'
        )));
    }

    public function test_method_returns_string()
    {
        $this->assertIsString(CheckSumService::unixsum('foo'));
    }

    public function test_method_returns_string_with_length_of_32()
    {
        $this->assertEquals(32, strlen(CheckSumService::unixsum('foo')));
    }

    public function test_method_returns_string_matching_expected_format()
    {
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', CheckSumService::unixsum('foo'));
    }

    public function test_method_returns_same_value_for_same_string_using_normal_method()
    {
        $this->assertEquals(md5('foo'), CheckSumService::unixsum('foo'));
    }

    public function test_method_returns_different_value_for_different_string()
    {
        $this->assertNotEquals(CheckSumService::unixsum('foo'), CheckSumService::unixsum('bar'));
    }

    public function test_function_is_case_sensitive()
    {
        $this->assertNotEquals(CheckSumService::unixsum('foo'), CheckSumService::unixsum('FOO'));
    }

    public function test_function_is_space_sensitive()
    {
        $this->assertNotEquals(CheckSumService::unixsum(' foo '), CheckSumService::unixsum('foo'));
    }

    public function test_method_returns_same_value_regardless_of_end_of_line_sequence()
    {
        $this->assertEquals(CheckSumService::unixsum('foo'), CheckSumService::unixsum('foo'));
        $this->assertEquals(CheckSumService::unixsum("foo\n"), CheckSumService::unixsum("foo\n"));
        $this->assertEquals(CheckSumService::unixsum("foo\n"), CheckSumService::unixsum("foo\r"));
        $this->assertEquals(CheckSumService::unixsum("foo\n"), CheckSumService::unixsum("foo\r\n"));
    }

    public function test_method_returns_same_value_for_string_with_mixed_end_of_line_sequences()
    {
        $this->assertEquals(CheckSumService::unixsum("foo\nbar\r\nbaz\r\n"),
            CheckSumService::unixsum("foo\nbar\nbaz\n"));
    }

    public function test_method_returns_same_value_when_loaded_from_file()
    {
        $string = "foo\nbar\r\nbaz\r\n";
        $file = tempnam(sys_get_temp_dir(), 'foo');
        file_put_contents($file, $string);

        $this->assertEquals(CheckSumService::unixsum($string), CheckSumService::unixsum(file_get_contents($file)));

        unlink($file);
    }

    public function test_method_returns_same_value_when_loaded_from_file_using_shorthand()
    {
        $string = "foo\nbar\r\nbaz\r\n";
        $file = tempnam(sys_get_temp_dir(), 'foo');
        file_put_contents($file, $string);

        $this->assertEquals(CheckSumService::unixsum($string), CheckSumService::unixsumFile($file));

        unlink($file);
    }
}
