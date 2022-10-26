<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Framework\Services\ChecksumService;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Services\ChecksumService
 */
class ChecksumServiceTest extends TestCase
{
    public function test_get_filecache()
    {
        $fileCacheService = new ChecksumService();
        $fileCache = $fileCacheService->getFilecache();

        $this->assertIsArray($fileCache);
        $this->assertArrayHasKey('/resources/views/layouts/app.blade.php', $fileCache);
        $this->assertArrayHasKey('unixsum', $fileCache['/resources/views/layouts/app.blade.php']);
        $this->assertEquals(32, strlen($fileCache['/resources/views/layouts/app.blade.php']['unixsum']));
    }

    public function test_get_checksums()
    {
        $fileCacheService = new ChecksumService();
        $checksums = $fileCacheService->getChecksums();

        $this->assertIsArray($checksums);
        $this->assertEquals(32, strlen($checksums[0]));
    }

    public function test_checksum_matches_any()
    {
        $fileCacheService = new ChecksumService();

        $this->assertTrue($fileCacheService->checksumMatchesAny(ChecksumService::unixsumFile(
            Hyde::vendorPath('resources/views/layouts/app.blade.php'))
        ));
    }

    public function test_checksum_matches_any_false()
    {
        $fileCacheService = new ChecksumService();

        $this->assertFalse($fileCacheService->checksumMatchesAny(ChecksumService::unixsum(
            'foo'
        )));
    }

    public function test_method_returns_string()
    {
        $this->assertIsString(ChecksumService::unixsum('foo'));
    }

    public function test_method_returns_string_with_length_of_32()
    {
        $this->assertEquals(32, strlen(ChecksumService::unixsum('foo')));
    }

    public function test_method_returns_string_matching_expected_format()
    {
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', ChecksumService::unixsum('foo'));
    }

    public function test_method_returns_same_value_for_same_string_using_normal_method()
    {
        $this->assertEquals(md5('foo'), ChecksumService::unixsum('foo'));
    }

    public function test_method_returns_different_value_for_different_string()
    {
        $this->assertNotEquals(ChecksumService::unixsum('foo'), ChecksumService::unixsum('bar'));
    }

    public function test_function_is_case_sensitive()
    {
        $this->assertNotEquals(ChecksumService::unixsum('foo'), ChecksumService::unixsum('FOO'));
    }

    public function test_function_is_space_sensitive()
    {
        $this->assertNotEquals(ChecksumService::unixsum(' foo '), ChecksumService::unixsum('foo'));
    }

    public function test_method_returns_same_value_regardless_of_end_of_line_sequence()
    {
        $this->assertEquals(ChecksumService::unixsum('foo'), ChecksumService::unixsum('foo'));
        $this->assertEquals(ChecksumService::unixsum("foo\n"), ChecksumService::unixsum("foo\n"));
        $this->assertEquals(ChecksumService::unixsum("foo\n"), ChecksumService::unixsum("foo\r"));
        $this->assertEquals(ChecksumService::unixsum("foo\n"), ChecksumService::unixsum("foo\r\n"));
    }

    public function test_method_returns_same_value_for_string_with_mixed_end_of_line_sequences()
    {
        $this->assertEquals(ChecksumService::unixsum("foo\nbar\r\nbaz\r\n"),
            ChecksumService::unixsum("foo\nbar\nbaz\n"));
    }

    public function test_method_returns_same_value_when_loaded_from_file()
    {
        $string = "foo\nbar\r\nbaz\r\n";
        $file = tempnam(sys_get_temp_dir(), 'foo');
        file_put_contents($file, $string);

        $this->assertEquals(ChecksumService::unixsum($string), ChecksumService::unixsum(file_get_contents($file)));

        unlink($file);
    }

    public function test_method_returns_same_value_when_loaded_from_file_using_shorthand()
    {
        $string = "foo\nbar\r\nbaz\r\n";
        $file = tempnam(sys_get_temp_dir(), 'foo');
        file_put_contents($file, $string);

        $this->assertEquals(ChecksumService::unixsum($string), ChecksumService::unixsumFile($file));

        unlink($file);
    }
}
